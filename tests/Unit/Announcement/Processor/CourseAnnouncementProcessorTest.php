<?php

namespace Tests\Unit\Announcement\Processor;

use Biz\Announcement\Processor\AnnouncementProcessorFactory;
use Biz\BaseTestCase;

class CourseAnnouncementProcessorTest extends BaseTestCase
{
    public function testCheckManage()
    {
        $processor = $this->_createCourseProcessor();

        $this->mockBiz('Course:CourseService', [
            [
                'functionName' => 'tryManageCourse',
                'returnValue' => [],
            ],
        ]);

        $result = $processor->checkManage(1);
        $this->assertTrue($result);
    }

    public function testCheckManageError()
    {
        $processor = $this->_createCourseProcessor();

        $result = $processor->checkManage(1);
        $this->assertFalse($result);
    }

    public function testCheckTake()
    {
        $processor = $this->_createCourseProcessor();

        $this->mockBiz('Course:CourseService', [
            [
                'functionName' => 'canTakeCourse',
                'returnValue' => true,
            ],
        ]);

        $result = $processor->checkTake(1);
        $this->assertTrue($result);
    }

    public function testGetTargetShowUrl()
    {
        $processor = $this->_createCourseProcessor();

        $result = $processor->getTargetShowUrl();
        $this->assertEquals('course_show', $result);
    }

    public function testAnnouncementNotificationIMDisabled()
    {
        $processor = $this->_createCourseProcessor();

        $this->mockBiz('Course:MemberService', [
            [
                'functionName' => 'countMembers',
                'returnValue' => 2,
            ],
            [
                'functionName' => 'findCourseStudents',
                'returnValue' => [['userId' => 1], ['userId' => 2]],
            ],
        ]);

        $this->mockBiz('System:SettingService', [
            [
                'functionName' => 'get',
                'returnValue' => ['enabled' => 0],
            ],
        ]);

        $this->mockBiz('User:NotificationService', [
            [
                'functionName' => 'notify',
                'returnValue' => true,
            ],
        ]);

        $result = $processor->announcementNotification(1, ['title' => 'announcement'], 'showurl', ['id' => 1]);
        $this->assertTrue($result);
    }

    public function testAnnouncementNotification()
    {
        $processor = $this->_createCourseProcessor();

        $this->mockBiz('Course:MemberService', [
            [
                'functionName' => 'countMembers',
                'returnValue' => 2,
            ],
            [
                'functionName' => 'findCourseStudents',
                'returnValue' => [['userId' => 1], ['userId' => 2]],
            ],
        ]);

        $this->mockBiz('Course:CourseService', [
            [
                'functionName' => 'getCourse',
                'returnValue' => ['id' => 1, 'title' => 'course title'],
            ],
        ]);

        $this->mockBiz('IM:ConversationService', [
            [
                'functionName' => 'getConversationByTarget',
                'returnValue' => ['no' => '1234567890'],
            ],
        ]);

        $this->mockBiz('System:SettingService', [
            [
                'functionName' => 'get',
                'returnValue' => ['enabled' => 1],
            ],
        ]);

        $this->mockBiz('User:NotificationService', [
            [
                'functionName' => 'notify',
                'returnValue' => true,
            ],
        ]);

        $this->mockBiz('Queue:QueueService', [
            [
                'functionName' => 'pushJob',
                'returnValue' => [],
            ],
        ]);

        $result = $processor->announcementNotification(1, ['title' => 'announcement'], 'showurl', ['id' => 1]);
        $this->assertTrue($result);
    }

    public function testIMEnabled()
    {
        $processor = $this->_createCourseProcessor();

        $this->mockBiz('System:SettingService', [
            [
                'functionName' => 'get',
                'returnValue' => ['enabled' => 1],
            ],
        ]);

        $result = $processor->isIMEnabled();

        $this->assertTrue($result);
    }

    public function testIMDisabled()
    {
        $processor = $this->_createCourseProcessor();

        $this->mockBiz('System:SettingService', [
            [
                'functionName' => 'get',
                'returnValue' => ['enabled' => 0],
            ],
        ]);

        $result = $processor->isIMEnabled();

        $this->assertFalse($result);
    }

    public function testTryManageObject()
    {
        $processor = $this->_createCourseProcessor();

        $this->mockBiz('Course:CourseService', [
            [
                'functionName' => 'tryManageCourse',
                'returnValue' => true,
            ],
        ]);

        $result = $processor->tryManageObject(1);

        $this->assertTrue($result);
    }

    public function testGetTargetObject()
    {
        $processor = $this->_createCourseProcessor();

        $this->mockBiz('Course:CourseService', [
            [
                'functionName' => 'getCourse',
                'returnValue' => ['id' => 1],
            ],
        ]);

        $result = $processor->getTargetObject(1);

        $this->assertEquals(1, $result['id']);
    }

    public function testGetActions()
    {
        $processor = $this->_createCourseProcessor();

        $result = $processor->getActions('create');
        $this->assertEquals('AppBundle:Course/Announcement:create', $result);

        $result = $processor->getActions('edit');
        $this->assertEquals('AppBundle:Course/Announcement:edit', $result);

        $result = $processor->getActions('list');
        $this->assertEquals('AppBundle:Course/Announcement:list', $result);
    }

    private function _createCourseProcessor()
    {
        $processor = new AnnouncementProcessorFactory($this->getBiz());

        return $processor->create('course');
    }
}
