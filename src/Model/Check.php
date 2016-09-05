<?php

namespace Guardian\Monitor\Model;

use Guardian\Monitor\Channel\ChannelInterface;

class Check
{
    protected $name;
    
    public function getName()
    {
        return $this->name;
    }
    
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    
    
    protected $command;
    
    public function getCommand()
    {
        return $this->command;
    }
    
    public function setCommand($command)
    {
        $this->command = (string)$command;
        return $this;
    }
    
    protected $interval;
    
    public function getInterval()
    {
        return $this->interval;
    }
    
    public function setInterval($interval)
    {
        $this->interval = (int)$interval;
        return $this;
    }
    
    protected $lastStamp = 0;
    public function getLastStamp()
    {
        return $this->lastStamp;
    }
    
    public function setLastStamp($lastStamp)
    {
        $this->lastStamp = $lastStamp;
        return $this;
    }
    
    public function getLastText()
    {
        return time() - $this->lastStamp . 's ago';
    }
    
    protected $channels = [];
    public function addChannel(ChannelInterface $channel)
    {
        $this->channels[$channel->getName()] = $channel;
    }
    
    public function getChannels()
    {
        return $this->channels;
    }
    
    public function hasChannel($name)
    {
        return isset($this->channels[$name]);
    }
}
