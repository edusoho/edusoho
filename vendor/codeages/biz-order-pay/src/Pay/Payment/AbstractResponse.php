<?php

namespace Codeages\Biz\Pay\Payment;

class AbstractResponse
{
    protected $success;
    protected $failData;

    public function __construct($success, $failData = '')
    {
        $this->success = $success;
        $this->failData = $failData;
    }

    public function isSuccessful()
    {
        return $this->success;
    }

    public function getFailData()
    {
        return $this->failData;
    }
}
