<?php

namespace QiQiuYun\SDK;

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
}