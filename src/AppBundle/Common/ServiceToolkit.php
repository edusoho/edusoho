<?php

namespace AppBundle\Common;

class ServiceToolkit
{
    private static $services = array(
        'homeworkReview' => array(
            'code' => 'homeworkReview',
            'shortName' => 'site.services.homeworkReview.shortName',
            'fullName' => 'site.services.homeworkReview.fullName',
            'summary' => 'site.services.homeworkReview.summary',
            'active' => 0,
        ),
        'testpaperReview' => array(
            'code' => 'testpaperReview',
            'shortName' => 'site.services.testpaperReview.shortName',
            'fullName' => 'site.services.testpaperReview.fullName',
            'summary' => 'site.services.testpaperReview.summary',
            'active' => 0,
        ),
        'teacherAnswer' => array(
            'code' => 'teacherAnswer',
            'shortName' => 'site.services.teacherAnswer.shortName',
            'fullName' => 'site.services.teacherAnswer.fullName',
            'summary' => 'site.services.teacherAnswer.summary',
            'active' => 0,
        ),
        'liveAnswer' => array(
            'code' => 'liveAnswer',
            'shortName' => 'site.services.liveAnswer.shortName',
            'fullName' => 'site.services.liveAnswer.fullName',
            'summary' => 'site.services.liveAnswer.summary',
            'active' => 0,
        ),
        'event' => array(
            'code' => 'event',
            'shortName' => 'site.services.event.shortName',
            'fullName' => 'site.services.event.fullName',
            'summary' => 'site.services.event.summary',
            'active' => 0,
        ),
        'workAdvise' => array(
            'code' => 'workAdvise',
            'shortName' => 'site.services.workAdvise.shortName',
            'fullName' => 'site.services.workAdvise.fullName',
            'summary' => 'site.services.workAdvise.summary',
            'active' => 0,
        ),
    );

    public static function getServicesByCodes($codes)
    {
        if (!is_array($codes)) {
            return array();
        }

        return array_values(ArrayToolkit::parts(static::$services, $codes));
    }
}
