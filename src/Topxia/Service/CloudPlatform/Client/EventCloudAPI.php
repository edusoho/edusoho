<?php
namespace Topxia\Service\CloudPlatform\Client;

class EventCloudAPI extends AbstractCloudAPI
{
    public function push($name, array $body = array(), $timestamp)
    {
        $event = array(
            "name"      => $name,
            "body"      => $body,
            'timestamp' => $timestamp
        );

        $event['user']      = $this->accessKey;
        $event["signature"] = $this->makeSignature($event);

        return parent::post('/events', $event);
    }

    public function makeSignature($event)
    {
        $text = http_build_query(['name' => $event['name'], 'timestamp' => $event['timestamp']]);
        $text .= "\n".$this->secretKey;
        return md5($text);
    }
}
