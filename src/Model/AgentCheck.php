<?php

namespace Guardian\Monitor\Model;

class AgentCheck
{
    protected $id;
    protected $changes = [];
    
    public function getId()
    {
        return $this->id;
    }
    
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
    
    protected $check;
    
    public function getCheck()
    {
        return $this->check;
    }
    
    public function setCheck(Check $check)
    {
        $this->check = $check;
        return $this;
    }
    
    protected $agent;
    
    public function getAgent()
    {
        return $this->agent;
    }
    
    public function setAgent($agent)
    {
        $this->agent = $agent;
        return $this;
    }
    
    protected $statusCode = -1;
    public function getStatusCode()
    {
        return $this->statusCode;
    }
    
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }
    
    
    protected $statusStamp;
    
    public function getStatusStamp()
    {
        return $this->statusStamp;
    }
    
    public function setStatusStamp($statusStamp)
    {
        $this->statusStamp = $statusStamp;
        return $this;
    }
    
    
    public function addChange(Change $change)
    {
        $changes[] = $change;
    }
    public function getChanges()
    {
        return $this->changes;
    }
}
