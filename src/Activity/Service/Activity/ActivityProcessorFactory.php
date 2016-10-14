<?php

namespace Activity\Service\Activity;

class ActivityProcessorFactory
{
    private static $processorMap = array();

    public static function getActivityProcessor($type)
    {
        if (empty(self::$processorMap[$type])) {
            $upperType                 = ucfirst($type);
            $class                     = __NAMESPACE__."\\Processor\\{$upperType}Processor";
            self::$processorMap[$type] = new $class();
        }

        return self::$processorMap[$type];
    }
}
