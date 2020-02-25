<?php
namespace Biz\Live\LiveStatisticsProcessor;

use Topxia\Service\Common\ServiceKernel;

class LiveStatsisticsProcessorFactory
{
    private static $mockedProcessor;

    public static function create($type)
    {
        if (!empty($mockedProcessor)) {
            return self::$mockedProcessor;
        }

        if (empty($type)) {
            throw new \Exception('type cannot be null');
        }

        $class = __NAMESPACE__.'\\'.ucfirst($type).'Processor';
        return new $class(ServiceKernel::instance()->getBiz());
    }
}