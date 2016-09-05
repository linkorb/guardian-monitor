<?php

namespace Guardian\Monitor\Loader;

use RuntimeException;

use Guardian\Monitor\Monitor;
use Guardian\Monitor\Model\Check;
use Symfony\Component\Yaml\Parser as YamlParser;

class YamlLoader
{
    public function loadYaml($filename)
    {

        if (!file_exists($filename)) {
            throw new RuntimeException("File not found: $filename");
        }

        $parser = new YamlParser();
        $data = $parser->parse(file_get_contents($filename));
        if (isset($data['include'])) {
            foreach ($data['include'] as $line) {
                $filenames = glob($line);
                if (count($filenames)==0) {
                    throw new RuntimeException("Include(s) not found: " . $line);
                }
                foreach ($filenames as $filename) {
                    if (!file_exists($filename)) {
                        throw new RuntimeException("Include filename does not exist: " . $filename);
                    }
                    $includeData = $this->loadYaml($filename);
                    $data = array_merge_recursive($data, $includeData);
                }
            }
        }
        return $data;
    }

    public function loadMonitor($data)
    {
        $monitorData = $data['monitor'];
        $monitor = new Monitor();
        $monitor->setName($monitorData['name']);
        if (isset($monitorData['port'])) {
            $monitor->setPort($monitorData['port']);
        }
        
        $stompConfig = [
            'host' => $monitorData['stomp']['address'],
            'vhost' => '/',
            'login' => $monitorData['stomp']['username'],
            'passcode' => $monitorData['stomp']['password']
        ];
        $monitor->setStompConfig($stompConfig);
        
        foreach ($data['channels'] as $name => $channelData) {
            $channelClass = '\\Guardian\\Monitor\\Channel\\' . ucfirst($channelData['type']) . 'Channel';
            $channel = new $channelClass($name, $channelData['arguments']);
            $monitor->addChannel($channel);
            if (isset($channelData['announce'])) {
                $channel->send('Starting Guardian Monitor on ' . $monitor->getName() . ' (Channel: ' . $name . ')');
            }
        }

        foreach ($data['checks'] as $name => $checkData) {
            $check = new Check();
            $check->setName($name);
            
            if (isset($checkData['command'])) {
                $check->setCommand($checkData['command']);
            }
            if (isset($checkData['interval'])) {
                $check->setInterval($checkData['interval']);
            }
            if (isset($checkData['channels'])) {
                
                $channelNames = $checkData['channels'];
                if (!is_array($channelNames)) {
                    throw new RuntimeException("channels on check " . $name . " is not an array");
                }
                foreach ($channelNames as $channelName) {
                    $channelName = trim($channelName);
                    $channel = $monitor->getChannel($channelName);
                    if (!$channel) {
                        throw new RuntimeException("Unknown channel $channelName for check $name");
                    }
                    $check->addChannel($channel);
                }
            }
            if (isset($checkData['groups'])) {
                $groups = $checkData['groups'];
                if (!is_array($groups)) {
                    throw new RuntimeException("groups on check " . $name . " is not an array");
                }
                foreach ($groups as $groupName) {
                    $check->addGroupName($groupName);
                }
            }
            $monitor->addCheck($check);
        }
        return $monitor;
    }
}
