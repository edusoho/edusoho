<?php

namespace Biz\Util;

use Biz\CloudPlatform\CloudAPIFactory;

class EdusohoTuiClient
{
    public function getToken()
    {
        $result = CloudAPIFactory::create('tui')->get('/token');

        return $result;
    }
}
