<?php

namespace Guardian\Monitor\Model;

class Change
{
    protected $stamp;

    public function getStamp()
    {
        return $this->stamp;
    }
    
    public function setStamp($stamp)
    {
        $this->stamp = $stamp;
        return $this;
    }
    
    public function getAgentCheck()
    {
        return $this->agentCheck;
    }
    
    public function setAgentCheck(AgentCheck $agentCheck)
    {
        $this->agentCheck = $agentCheck;
        return $this;
    }
    
    public function getLastStatusCode()
    {
        return $this->lastStatusCode;
    }
    
    public function setLastStatusCode($lastStatusCode)
    {
        $this->lastStatusCode = $lastStatusCode;
        return $this;
    }
    
    public function getNewStatusCode()
    {
        return $this->newStatusCode;
    }
    
    public function setNewStatusCode($newStatusCode)
    {
        $this->newStatusCode = $newStatusCode;
        return $this;
    }
}
