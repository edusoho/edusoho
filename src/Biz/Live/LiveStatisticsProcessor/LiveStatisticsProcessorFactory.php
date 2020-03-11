<?php

namespace Biz\Live\LiveStatisticsProcessor;

use Topxia\Service\Common\ServiceKernel;

class LiveStatisticsProcessorFactory
{
    private static $mockedProcessor;

    private static $map = array(
        'checkin',
        'visitor',
    );

    public static function create($type)
    {
        if (!empty(self::$mockedProcessor)) {
            return self::$mockedProcessor;
        }

        if (empty($type)) {
            throw new \Exception('type cannot be null');
        }

        if (in_array($type, self::$map)) {
            $class = __NAMESPACE__.'\\'.ucfirst($type).'Processor';

            return new $class(ServiceKernel::instance()->getBiz());
        }
    }
}
