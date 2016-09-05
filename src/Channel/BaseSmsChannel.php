<?php

namespace Guardian\Monitor\Channel;

use Xi\Sms\SmsService;
use Xi\Sms\SmsMessage;

abstract class BaseSmsChannel
{
    protected $service;
    protected $gateway;
    protected $name;
    protected $from;
    protected $recipients=[];
    
    // it's the constructor's job to setup this->gateway
    public function __construct($name, $arguments)
    {
        if (!$this->gateway) {
            throw new RuntimeException("Child-class failed to setup this->gateway");
        }
        $this->name = $name;
        $this->service = new SmsService($this->gateway);
        $this->from = $arguments['from'];
        $this->recipients = $arguments['recipients'];
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function send($text)
    {
        foreach ($this->recipients as $recipient) {
            print_r($recipient);
            $message = new SmsMessage($text, $this->from, $recipient);
            $this->service->send($message);
        }
    }
}
