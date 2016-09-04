<?php

namespace Guardian\Monitor\Channel;

use HipChat\HipChat;

class HipchatChannel implements ChannelInterface
{
    protected $hipchat;
    protected $name;
    protected $room; // Room ID or Room Name
    protected $from; // Name displayed in the hipchat message
    
    public function __construct($name, $arguments)
    {
        $this->hipchat = new HipChat($arguments['token']);
        $this->name = $name;
        $this->room = $arguments['room'];
        $this->from = $arguments['from'];
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function send($message)
    {
        $notify = true;
        $color = 'yellow';
        $this->hipchat->message_room($this->room, $this->from, $message, $notify, $color);
    }
}
