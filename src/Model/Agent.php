<?php

namespace Guardian\Monitor\Model;

class Agent
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
    
    protected $groups = [];
    
    public function getGroups()
    {
        return $this->groups;
    }
    
    public function setGroups($groups)
    {
        $this->groups = $groups;
        return $this;
    }
    
    public function getGroupsString()
    {
        return implode(',', $this->groups);
    }
    
    protected $lastSeen;
    
    public function getLastSeen()
    {
        return $this->lastSeen;
    }
    
    public function setLastSeen($lastSeen)
    {
        $this->lastSeen = $lastSeen;
        return $this;
    }
    
    
    protected $agentChecks = [];
    public function addAgentCheck(AgentCheck $agentCheck)
    {
        $this->agentChecks[] = $agentCheck;
    }
    
    public function getAgentChecks()
    {
        return $this->agentChecks;
    }
    
    protected $mute = false;
    
    public function isMute()
    {
        return $this->mute;
    }
    
    public function setMute($mute)
    {
        $this->mute = $mute;
        return $this;
    }
    
}
