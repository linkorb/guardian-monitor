<?php

namespace Guardian\Monitor\Channel;

class SmtpChannel implements ChannelInterface
{
    protected $smtp;
    protected $name;
    
    public function __construct($name, $arguments)
    {
        $this->smtp = null;
        $this->name = $name;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function send($message)
    {
        echo "SMTP NOT YET IMPLEMENTED\n";
    }
}
