<?php

namespace Guardian\Monitor;

use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Guardian\Monitor\Model\CheckRequest;
use Guardian\Monitor\Model\Check;
use Guardian\Monitor\Model\Agent;
use Guardian\Monitor\Model\Change;
use Guardian\Monitor\Model\AgentCheck;
use Guardian\Monitor\Channel\ChannelInterface;

class Monitor
{
    protected $name;
    
    protected $checkRequests = [];
    protected $changes = [];
    protected $channels = [];
    
    public function getCheckRequests()
    {
        return $this->checkRequests;
    }
    
    public function getAgentCheckIssues()
    {
        $res = [];
        foreach ($this->agents as $agent) {
            foreach ($agent->getAgentChecks() as $agentCheck) {
                if ($agentCheck->getStatusCode()>0) {
                    $res[] = $agentCheck;
                }
            }
        }
        return $res;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    
    protected $port = 8080;
    
    public function getPort()
    {
        return $this->port;
    }
    
    public function setPort($port)
    {
        $this->port = $port;
        return $this;
    }
    
    public function addChange(Change $change)
    {
        $this->changes[] = $change;
    }
    
    public function getChanges()
    {
        return $this->changes;
    }
    
    public function addChannel(ChannelInterface $channel)
    {
        $this->channels[$channel->getName()] = $channel;
    }
    
    public function getChannels()
    {
        return $this->channels;
    }
    public function getChannel($name)
    {
        return $this->channels[$name];
    }
    
    protected $checks = [];
    public function addCheck(Check $check)
    {
        if (!$check->getName()) {
            throw new RuntimeException("Can't add a check without a name");
        }
        $this->checks[$check->getName()] = $check;
    }
    
    public function getChecks()
    {
        return $this->checks;
    }
    
    public function getCheck($checkName)
    {
        return $this->checks[$checkName];
    }
    
    public function tick()
    {
        foreach ($this->checks as $check) {
            $now = time();
            if ($now > ($check->getLastStamp() + $check->getInterval())) {
                $this->runCheck($check);
                $check->setLastStamp($now);
            }
        }
        $this->cleanupRequests();
        $this->tagTimeouts();
        $this->reportChanges();
        $this->scheduleNextTick();
    }
    
    public function tagTimeouts()
    {
        foreach ($this->agents as $agent) {
            foreach ($agent->getAgentChecks() as $agentCheck) {
                $lastStatusCode = $agentCheck->getStatusCode();
                $newStatusCode = $lastStatusCode;

                $timeout = 3 * $agentCheck->getCheck()->getInterval();
                
                if ($agentCheck->getStatusStamp()>0) {
                    if (time() - $agentCheck->getStatusStamp()>$timeout) {
                        $newStatusCode = 3;
                    }
                }
                
                if ($lastStatusCode != $newStatusCode) {
                    $change = new Change();
                    $change->setStamp(time());
                    $change->setLastStatusCode($lastStatusCode);
                    $change->setNewStatusCode($newStatusCode);
                    $change->setAgentCheck($agentCheck);
                    $agentCheck->addChange($change);
                    $this->addChange($change);
                    $agentCheck->setStatusCode($newStatusCode);
                    $agentCheck->setStatusStamp(time());
                }
            }
        }
    }
    protected $lastReportChangesStamp = 0;
    public function reportChanges()
    {
        if (time() - $this->lastReportChangesStamp < 10) {
            return;
        }
        
        $lastStamp = $this->lastReportChangesStamp;
        $this->lastReportChangesStamp = time();
        foreach ($this->channels as $channel) {
            $res = [];
            foreach ($this->changes as $change) {
                if ($change->getStamp()>=$lastStamp) {
                    if ($change->getLastStatusCode()>=-1) {
                        if ($change->getAgentCheck()->getCheck()->hasChannel($channel->getName())) {
                            $res[] = $change;
                        }
                    }
                }
            }
            
            if (count($res)>0) {
                $ok = 0;
                $warn = 0;
                $critical = 0;
                $timeout = 0;
                $agentNames = [];
                $checkNames = [];
                foreach ($res as $change) {
                    switch ($change->getNewStatusCode()) {
                        case '3':
                            $timeout++;
                            break;
                        case '2':
                            $critical++;
                            break;
                        case '1':
                            $warn++;
                            break;
                        case '0':
                            $ok++;
                            break;
                    }
                    $agentNames[] = $change->getAgentCheck()->getAgent()->getName();
                    $checkNames[] = $change->getAgentCheck()->getCheck()->getName();
                }
                $agentNames = array_unique($agentNames);
                $checkNames = array_unique($checkNames);
                
                $color = 'gray';
                $out = '';
                if ($ok>0) {
                    $out .= 'OK+' . $ok . ' ';
                    $color = 'green';
                }
                if ($warn>0) {
                    $out .= 'WARN+' . $warn . ' ';
                    $color = 'yellow';
                }
                if ($critical>0) {
                    $out .= 'CRITICAL+' . $critical . ' ';
                    $color = 'red';
                }
                if ($timeout>0) {
                    $out .= 'TIMEOUT+' . $timeout . ' ';
                    $color = 'red';
                }
                
                if (count($agentNames)>1) {
                    $out .= count($agentNames) . ' agents ';
                } else {
                    $out .= $agentNames[0] . ' ';
                }
                if (count($checkNames)>1) {
                    $out .= count($checkNames) . ' checks ';
                } else {
                    $out .= $checkNames[0] . ' ';
                }
                echo 'Channel ' . $channel->getName() . ': ' . trim($out) . "\n";
                $channel->send($out);
            }
        }
    }
    
    public function cleanupRequests()
    {
        $buffer = 60*5; // seconds
        foreach ($this->checkRequests as $request) {
            if ($request->getRequestStamp()< (time()-$buffer)) {
                $id = $request->getId();
                unset($this->checkRequests[$id]);
            }
        }
    }
    
    public function getRequest($id)
    {
        return $this->checkRequests[$id];
    }
    
    public function runCheck(Check $check)
    {
        foreach ($this->agents as $agent) {
            $checkRequest = new CheckRequest();
            $id = uniqid();
            $checkRequest->setId($id);
            $checkRequest->setRequestStamp(time());
            $checkRequest->setCheck($check);
            $checkRequest->setAgent($agent);
            $this->checkRequests[$id] = $checkRequest;
            
            $message = [
                'type' => 'check_request',
                'from' => 'monitor',
                'payload' => [
                    'requestId' => $checkRequest->getId(),
                    'check' => $checkRequest->getCheck()->getName(),
                    'command' => $check->getCommand()
                ]
            ];
            //print_r($message);
            $this->stomp->send('/topic/agent:' . $agent->getName(), json_encode($message));
        }
    }

    public function scheduleNextTick()
    {
        $this->loop->addTimer(
            0.1,
            function () {
                $this->tick();
            }
        );
    }
    
    protected $agents = [];
    public function hasAgent($name)
    {
        return isset($this->agents[$name]);
    }
    
    public function getAgent($name)
    {
        return $this->agents[$name];
    }
    
    public function addAgent(Agent $agent)
    {
        $this->agents[$agent->getName()] = $agent;
        foreach ($this->getChecks() as $check) {
            $agentCheck = new AgentCheck();
            $agentCheck->setId(uniqid());
            $agentCheck->setCheck($check);
            $agentCheck->setAgent($agent);
            $agent->addAgentCheck($agentCheck);
        }
    }
    
    public function getAgents()
    {
        return $this->agents;
    }
    
    public function processMessage($message)
    {
        $data = json_decode($message, true);
        if (!$data) {
            echo "Invalid JSON: " . $message . "\n";
            return;
        }
        switch ($data['type']) {
            case 'status':
                $from = $data['from'];
                if (!$this->hasAgent($from)) {
                    $agent = new Agent();
                    $agent->setName($from);
                    $this->addAgent($agent);
                }
                $agent = $this->getAgent($from);
                $agent->setLastSeen(time());
                break;
            case 'check-response':
                $from = $data['from'];
                $requestId = $data['payload']['requestId'];
                if (!isset($this->checkRequests[$requestId])) {
                    return;
                }
                $request = $this->checkRequests[$requestId];
                $request->setStatusCode($data['payload']['statusCode']);
                $request->setResponseStamp(time());
                $agent = $this->getAgent($from);
                foreach ($agent->getAgentChecks() as $agentCheck) {
                    if ($agentCheck->getCheck()->getName()==$request->getCheck()->getName()) {
                        $lastStatusCode = $agentCheck->getStatusCode();
                        $newStatusCode = $data['payload']['statusCode'];
                        if ($lastStatusCode != $newStatusCode) {
                            $change = new Change();
                            $change->setStamp(time());
                            $change->setLastStatusCode($lastStatusCode);
                            $change->setNewStatusCode($newStatusCode);
                            $change->setAgentCheck($agentCheck);
                            $agentCheck->addChange($change);
                            $this->addChange($change);
                        }
                        $agentCheck->setStatusCode($newStatusCode);
                        $agentCheck->setStatusStamp(time());
                    }
                }
                break;
            default:
                echo "Unsupported message type: " . $data['type'];
                break;
        }
    }
    
    protected $loop;
    protected $socket;
    protected $http;
    protected $stomp;
    protected $stompConfig = [];
    
    public function setStompConfig($config)
    {
        $this->stompConfig = $config;
    }
    
    public function run()
    {
        $this->loop = \React\EventLoop\Factory::create();
        $this->socket = new \React\Socket\Server($this->loop);
        $this->http = new \React\Http\Server($this->socket, $this->loop);
        
        // Setup stomp
        $stompFactory = new \React\Stomp\Factory($this->loop);
        $this->stomp = $stompFactory->createClient(
            $this->stompConfig
        );
        $this->stomp->connect();
        
        $this->stomp->subscribe('/topic/monitor', function ($frame) {
            echo "Message received: {$frame->body}\n";
            $this->processMessage((string)$frame->body);
        });
        
        
        $requestHandler = new RequestHandler($this);
        $this->http->on('request', [$requestHandler, 'handle']);
        $this->socket->listen($this->getPort(), '0.0.0.0');
        $this->scheduleNextTick();

        $this->loop->run();
    }
}
