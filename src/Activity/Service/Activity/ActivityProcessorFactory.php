<?php

namespace Activity\Service\Activity;

class ActivityProcessorFactory
{
    private static $processorMap = array();

    public static function getActivityProcessor($type)
    {
        if (empty(self::$processorMap[$type])) {
            $upperType = ucfirst($type);
            $class     = __NAMESPACE__."\\Processor\\{$upperType}Processor";
            if (class_exists($class)) {
                self::$processorMap[$type] = new $class();
            } else {
                self::$processorMap[$type] = array();
            }
        }

        return self::$processorMap[$type];
    }
}
