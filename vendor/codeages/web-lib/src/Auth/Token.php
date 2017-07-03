<?php

namespace Codeages\Weblib\Auth;

class Token
{
    public $keyId;

    public $keySecret;

    public $deadline;

    public $once;

    public $signature;

    public function __construct($keyId, $keySecret = '', $deadline = 0, $once = '', $signature = '')
    {
        $this->keyId = $keyId;
        $this->keySecret = $keySecret;
        $this->deadline = $deadline;
        $this->once = $once;
        $this->signature = $signature;
    }

    public function isExpired()
    {
        return $this->deadline > 0 && $this->deadline < time();
    }

    public function isReplay()
    {
        return false;
    }
}