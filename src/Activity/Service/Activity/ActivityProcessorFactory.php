<?php

namespace Activity\Service\Activity;

class ActivityProcessorFactory
{
    private static $processorMap = array();

    public static function getActivityProcessor($type)
    {
        $types = self::getActivityTypes();
        if (!in_array($type, array_keys($types))) {
            throw new \InvalidArgumentException('activity type is invalid');
        }

        if (empty(self::$processorMap[$type])) {
            if (!empty($types[$type]['processor']) && class_exists($types[$type]['processor'])) {
                $class                     = $types[$type]['processor'];
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
                'processor'    => '',
                'create_modal' => 'ActivityBundle:ActivityManage:text.html.twig',
                'show_page'    => 'ActivityBundle:Activity:text-show.html.twig',
                'events'       => array(
                    'finish' => 'Activity\\Service\\Activity\\EventChain\\ActivityFinish'
                )
            )
        );
    }
}
