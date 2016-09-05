<?php

namespace Guardian\Monitor;

use LightnCandy\LightnCandy;

class RequestHandler
{
    
    public $monitor;
    
    public function __construct(Monitor $monitor)
    {
        $this->monitor = $monitor;
    }
    
    public function handle($request, $response)
    {
        $q = $request->getQuery();
        $controller='index';
        
        if (isset($q['controller'])) {
            $controller = $q['controller'];
        }
        $controllerName = $controller . 'Controller';
        $this->$controllerName($request, $response);
    }
    
    private function indexController($request, $response)
    {
        $response->writeHead(200, array('Content-Type' => 'text/html'));
        $html = $this->render('index.html.hbs', ['agents' => $this->monitor->getAgents()]);
        $response->end($html);
    }
    
    private function checkController($request, $response)
    {
        $response->writeHead(200, array('Content-Type' => 'text/html'));
        $checkName = $request->getQuery()['checkName'];
        $check = $this->monitor->getCheck($checkName);
        $agents = [];
        foreach ($this->monitor->getAgents() as $agent) {
            if ($agent->hasCheck($checkName)) {
                $agents[] = $agent;
            }
        }
        $html = $this->render('check.html.hbs', ['check' => $check, 'agents' => $agents]);
        $response->end($html);
    }
    
    private function agentController($request, $response)
    {
        $response->writeHead(200, array('Content-Type' => 'text/html'));
        $agentName = $request->getQuery()['agentName'];
        $agent = $this->monitor->getAgent($agentName);
        $html = $this->render('agent.html.hbs', ['agent' => $agent]);
        $response->end($html);
    }
        

    private function requestController($request, $response)
    {
        $response->writeHead(200, array('Content-Type' => 'text/html'));
        $requestId = $request->getQuery()['requestId'];
        $request = $this->monitor->getRequest($requestId);
        $html = $this->render('request.html.hbs', ['request' => $request]);
        $response->end($html);
    }
    
    private function render($templateName, $data, $wrap = true)
    {
        $data['monitor'] = $this->monitor;
        $template = file_get_contents(__DIR__ . '/../templates/' . $templateName);

        $php = LightnCandy::compile($template, array(
            "flags" => LightnCandy::FLAG_ERROR_EXCEPTION|LightnCandy::FLAG_METHOD
        ));
            
        $renderer = LightnCandy::prepare($php);
        try {
            $html = $renderer($data);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        if ($wrap) {
            $html = $this->render('layout.html.hbs', ['content' => $html], false);
        }
        return $html;
    }
    
    
    public function wrapLayout($content)
    {
        $template = file_get_contents(__DIR__ . '/../templates/layout.html.hbs');
        
        $php = LightnCandy::compile($template, array(
            "flags" => LightnCandy::FLAG_ERROR_EXCEPTION|LightnCandy::FLAG_METHOD
        ));
            
        $renderer = LightnCandy::prepare($php);
        $variables = ['agent' => $this->agent, 'content' => $content];
        try {
            $html = $renderer($variables);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return $html;
    }
}
