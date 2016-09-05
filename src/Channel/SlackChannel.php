<?php

namespace Guardian\Monitor\Channel;

use Maknz\Slack\Client;

class SlackChannel implements ChannelInterface
{
    protected $slack;
    protected $name;
    
    public function __construct($name, $arguments)
    {
        $url = $arguments['url'];
        $settings = [
            'username' => $arguments['from'],
            'channel' => $arguments['channel']
        ];
        $this->name = $name;
        $this->slack = new Client($url, $settings);
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function send($message)
    {
        
        $this->slack->send($message);
    }
}
