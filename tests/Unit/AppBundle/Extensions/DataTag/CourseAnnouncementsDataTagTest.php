<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\CourseAnnouncementsDataTag;
use AppBundle\Common\ReflectionUtils;

class CourseAnnouncementsDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $mockedTime = 1515302767;

        $this->mockBiz(
            'Announcement:AnnouncementService',
            array(
                array(
                    'functionName' => 'searchAnnouncements',
                    'returnValue' => array(
                        array(
                            'id' => 1,
                            'userId' => 1,
                            'targetType' => 'course',
                            'url' => 'http://www.dev-edusoho.com/course/1',
                            'contents' => 'course contents 11',
                        ),
                        array(
                            'id' => 2,
                            'userId' => 1,
                            'targetType' => 'course',
                            'url' => 'http://www.dev-edusoho.com/course/1',
                            'contents' => 'course contents 12',
                        ),
                    ),
                    'withParams' => array(
                        array(
                            'targetType' => 'course',
                            'endTime' => $mockedTime,
                            'targetId' => 1,
                        ),
                        array('createdTime' => 'DESC'),
                        0,
                        2,
                    ),
                ),
            )
        );

        $arguments = array(
            'count' => 2,
            'courseId' => 1,
        );
        $dataTag = new CourseAnnouncementsDataTag();
        $dataTag = ReflectionUtils::setProperty($dataTag, 'mockedTime', $mockedTime);
        $announcementsData = $dataTag->getData($arguments);

        $expect = array(
            'id' => 1,
            'userId' => 1,
            'targetType' => 'course',
            'url' => 'http://www.dev-edusoho.com/course/1',
            'contents' => 'course contents 11',
        );
        $this->assertEquals(2, count($announcementsData));
        $this->assertArrayEquals($expect, $announcementsData[0]);
    }
}
