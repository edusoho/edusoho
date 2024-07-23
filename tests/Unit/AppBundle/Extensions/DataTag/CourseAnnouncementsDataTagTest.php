<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use AppBundle\Common\TimeMachine;
use AppBundle\Extensions\DataTag\CourseAnnouncementsDataTag;
use Biz\BaseTestCase;

class CourseAnnouncementsDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $mockedTime = 1515302767;

        $this->mockBiz(
            'Announcement:AnnouncementService',
            [
                [
                    'functionName' => 'searchAnnouncements',
                    'returnValue' => [
                        [
                            'id' => 1,
                            'userId' => 1,
                            'targetType' => 'course',
                            'url' => 'http://www.dev-edusoho.com/course/1',
                            'contents' => 'course contents 11',
                        ],
                        [
                            'id' => 2,
                            'userId' => 1,
                            'targetType' => 'course',
                            'url' => 'http://www.dev-edusoho.com/course/1',
                            'contents' => 'course contents 12',
                        ],
                    ],
                    'withParams' => [
                        [
                            'targetType' => 'course',
                            'endTime_GTE' => $mockedTime,
                            'startTime_LTE' => $mockedTime,
                            'targetId' => 1,
                        ],
                        ['createdTime' => 'DESC'],
                        0,
                        2,
                    ],
                ],
            ]
        );

        $arguments = [
            'count' => 2,
            'courseId' => 1,
        ];
        $dataTag = new CourseAnnouncementsDataTag();
        TimeMachine::setMockedTime($mockedTime);
        $announcementsData = $dataTag->getData($arguments);

        $expect = [
            'id' => 1,
            'userId' => 1,
            'targetType' => 'course',
            'url' => 'http://www.dev-edusoho.com/course/1',
            'contents' => 'course contents 11',
        ];
        $this->assertEquals(2, count($announcementsData));
        $this->assertArrayEquals($expect, $announcementsData[0]);
    }
}
