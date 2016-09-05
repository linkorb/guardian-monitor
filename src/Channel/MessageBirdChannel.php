<?php

namespace Guardian\Monitor\Channel;

use Xi\Sms\Gateway\MessageBirdGateway;

class MessageBirdChannel extends BaseSmsChannel implements ChannelInterface
{
    
    protected $name;
    
    public function __construct($name, $arguments)
    {
        $this->gateway = new MessageBirdGateway($arguments['apikey']);
        parent::__construct($name, $arguments);
    }
}
