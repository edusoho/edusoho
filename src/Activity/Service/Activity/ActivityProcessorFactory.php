<?php

namespace Activity\Service\Activity;

class ActivityProcessorFactory
{
    private static $processorMap = array();

    public static function getActivityProcessor($type)
    {
        if (!in_array($type, array_keys(self::getActivityTypes()))) {
            throw new \InvalidArgumentException('activity type is invalid');
        }

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

    public static function getEvent($eventName)
    {
        $eventNames = explode('.', $eventName);
        $types      = self::getActivityTypes();

        if (empty($types[$eventNames[0]]['events'][$eventNames[1]])) {
            return;
        }

        $eventClass = $types[$eventNames[0]]['events'][$eventNames[1]];
        return new $eventClass();
    }

    public static function getActivityTypeConfig($type)
    {
        $types = self::getActivityTypes();
        return $types[$type];
    }

    public static function getActivityTypes()
    {
        return array(
            'text' => array(
                'name'         => '图文',
                'create_modal' => 'ActivityBundle:ActivityManage:text.html.twig',
                'show_page'    => 'ActivityBundle:Activity:text-show.html.twig',
                'events'       => array()
            )
        );
    }
}
