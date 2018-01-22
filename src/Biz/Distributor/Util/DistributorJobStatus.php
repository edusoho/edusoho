<?php

namespace Biz\Distributor\Util;

class DistributorJobStatus
{
    const PENDING = 'pending'; //可以发

    const FINISHED = 'finished'; //已发送

    const ERROR = 'error'; //错误，需重新发送

    public static function getSendableStatus()
    {
        return array(self::PENDING, self::ERROR);
    }
}
