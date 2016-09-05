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
    
    protected $groupNames = [];
    
    public function getGroupNames()
    {
        return $this->groupNames;
    }
    
    public function addGroupName($groupName)
    {
        $this->groupNames[] = $groupName;
        $this->groupNames = array_unique($this->groupNames);
        return $this;
    }
    
    public function hasGroupName($groupName)
    {
        return in_array($groupName, $this->groupNames);
    }
    
    public function getGroupNamesString()
    {
        return implode(',', $this->groupNames);
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
    
    public function hasCheck($name)
    {
        foreach ($this->getAgentChecks() as $agentCheck) {
            if ($agentCheck->getCheck()->getName()==$name) {
                return true;
            }
        }
        return false;
    }
    
}
