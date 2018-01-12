<?php

namespace Biz\Distributor\Util;

class DistributorJobStatus
{
    public static $PENDING = 'pending'; //可以发

    public static $FINISHED = 'finished'; //已发送

    public static $ERROR = 'error'; //错误，需重新发送

    public static function getSendableStatus()
    {
        return array(self::$PENDING, self::$ERROR);
    }
}
