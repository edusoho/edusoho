<?php
namespace Topxia\Service\CloudPlatform\Client;

class EventCloudAPI extends AbstractCloudAPI
{

    public function post($name, array $body = array(), $timestamp)
    {
    	$event = array(
		    "name"=> $name,
		    "body"=>$body,
		    'timestamp' => $timestamp
		);

		$event['user'] => $this->accessKey;
		$event["signature"]=> $this->makeSignature($event);

        return parent::$this->_request('POST', $this->apiUrl, $event, array());
    }

    public function makeSignature($event)
    {
        $text = http_build_query(['name' => $event['name'], 'timestamp' => $event['timestamp']]);
        $text .= "\n".$this->secretKey;
        return md5($text);
    }
}
