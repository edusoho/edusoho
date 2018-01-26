<?php

namespace QiQiuYun\SDK;

use QiQiuYun\SDK;

class Auth
{
    protected $accessKey;

    protected $secretKey;

    public function __construct($accessKey, $secretKey)
    {
        $this->accessKey = $accessKey;
        $this->secretKey = $secretKey;
    }

    public function getAccessKey()
    {
        return $this->accessKey;
    }

    public function sign($signingText)
    {
        $signature = hash_hmac('sha1', $signingText, $this->secretKey, true);

        return  str_replace(array('+', '/'), array('-', '_'), base64_encode($signature));
    }

    public function generateSignature($deadline, $uri, $body, $useOnce = true)
    {
        $once = $useOnce ? SDK\random_str('16') : 'no';
        $signingText = "{$once}\n{$deadline}\n{$uri}\n{$body}";
        $sign = $this->sign($signingText);
        return "{$this->accessKey}:{$deadline}:{$once}:{$sign}";
    }
}
