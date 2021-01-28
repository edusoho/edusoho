<?php

namespace Tests\Unit\AppBundle\Common;

use Biz\BaseTestCase;
use AppBundle\Common\ServiceToolkit;

class ServiceToolkitTest extends BaseTestCase
{
    public function testGetServicesByCodes()
    {
        $result = ServiceToolkit::getServicesByCodes('notArrayTest');
        $this->assertEquals(true, empty($result));
        $result = ServiceToolkit::getServicesByCodes(array('notExistsTest'));
        $this->assertEquals(true, empty($result));
        $result = ServiceToolkit::getServicesByCodes(array(
            'homeworkReview',
            'testpaperReview',
            'teacherAnswer',
            'liveAnswer',
            'event',
            'workAdvise',
        ));
        $this->assertArrayEquals(array(
            array(
                'code' => 'homeworkReview',
                'shortName' => 'site.services.homeworkReview.shortName',
                'fullName' => 'site.services.homeworkReview.fullName',
                'summary' => 'site.services.homeworkReview.summary',
                'active' => 0,
            ),
            array(
                'code' => 'testpaperReview',
                'shortName' => 'site.services.testpaperReview.shortName',
                'fullName' => 'site.services.testpaperReview.fullName',
                'summary' => 'site.services.testpaperReview.summary',
                'active' => 0,
            ),
            array(
                'code' => 'teacherAnswer',
                'shortName' => 'site.services.teacherAnswer.shortName',
                'fullName' => 'site.services.teacherAnswer.fullName',
                'summary' => 'site.services.teacherAnswer.summary',
                'active' => 0,
            ),
            array(
                'code' => 'liveAnswer',
                'shortName' => 'site.services.liveAnswer.shortName',
                'fullName' => 'site.services.liveAnswer.fullName',
                'summary' => 'site.services.liveAnswer.summary',
                'active' => 0,
            ),
            array(
                'code' => 'event',
                'shortName' => 'site.services.event.shortName',
                'fullName' => 'site.services.event.fullName',
                'summary' => 'site.services.event.summary',
                'active' => 0,
            ),
            array(
                'code' => 'workAdvise',
                'shortName' => 'site.services.workAdvise.shortName',
                'fullName' => 'site.services.workAdvise.fullName',
                'summary' => 'site.services.workAdvise.summary',
                'active' => 0,
            ),
        ), $result);
    }
}
