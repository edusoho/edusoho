<?php

namespace Omnipay\Alipay\Responses;

use Omnipay\Common\Message\AbstractResponse as Response;

abstract class AbstractResponse extends Response
{

    public function data($key = null, $default = null)
    {
        if (is_null($key)) {
            return $this->data;
        } else {
            return array_get($this->data, $key, $default);
        }
    }
}
