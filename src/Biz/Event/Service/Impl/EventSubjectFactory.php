<?php

namespace Biz\Event\Service\Impl;

use Topxia\Service\Common\ServiceKernel;

class EventSubjectFactory
{
    private static $map = array(
        'course',
        'classroom',
        'courseMember',
        'task',
    );

    public static function create($subjectType)
    {
        if (in_array($subjectType, self::$map)) {
            $class = __NAMESPACE__.'\\'.ucfirst($subjectType).'Subject';

            return new $class(ServiceKernel::instance()->getBiz());
        }
    }
}
