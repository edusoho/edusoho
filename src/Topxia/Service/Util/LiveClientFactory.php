<?php

namespace Topxia\Service\Util;

use Topxia\Service\Common\ServiceKernel;

class LiveClientFactory
{

    public static function createClient()
    {

        $class = __NAMESPACE__ . '\\EdusohoLiveClient';

        $client = new $class();

        return $client;
    }

}