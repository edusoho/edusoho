<?php

namespace Codeages\Weblib\Auth;

class AccessKey
{
    public $id;

    public $secret;

    public $status;

    public $expiredTime;

    public function __construct($id, $secret = '', $status = 'active', $expiredTime = 0)
    {
        $this->id = $id;
        $this->secret = $secret;
        $this->status = $status;
        $this->expiredTime = $expiredTime;
    }

    public function isActive()
    {
        return $this->status == 'active';
    }

    public function isInactive()
    {
        return $this->status == 'inactive';
    }

    public function isDeleted()
    {
        return $this->status == 'deleted';
    }

    public function isExpired()
    {
        return $this->expiredTime > 0 && $this->expiredTime < time();
    }
}