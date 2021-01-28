<?php

namespace Codeages\Biz\Pay\Message;

class AbstractResponse
{
    protected $success;
    protected $message;

    public function __construct($success, $message='')
    {
        $this->success = $success;
        $this->message = $message;
    }

    public function isSuccessful()
    {
        return $this->success;
    }

    public function getMessage()
    {
        return $this->message;
    }
}