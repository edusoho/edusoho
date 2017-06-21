<?php

namespace Codeages\Weblib\Auth;

class Token
{
    public $keyId;

    public $keySecret;

    public $signature;

    public function __construct($keyId, $keySecret = '', $signature = '')
    {
        $this->keyId = $keyId;
        $this->keySecret = $keySecret;
        $this->signature = $signature;
    }
}