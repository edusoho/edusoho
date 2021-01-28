<?php

namespace Tests\Unit\Announcement\Processor;

use Biz\BaseTestCase;
use Biz\Announcement\Processor\AnnouncementProcessorFactory;

class ClassroomAnnouncementProcessorTest extends BaseTestCase
{
    public function testCheckManage()
    {
        $processor = $this->_createClassroomProcessor();

        $this->mockBiz('Classroom:ClassroomService', array(
            array(
                'functionName' => 'canManageClassroom',
                'returnValue' => true,
            ),
        ));

        $result = $processor->checkManage(1);
        $this->assertTrue($result);
    }

    public function testCheckTake()
    {
        $processor = $this->_createClassroomProcessor();

        $this->mockBiz('Classroom:ClassroomService', array(
            array(
                'functionName' => 'canTakeClassroom',
                'returnValue' => true,
            ),
        ));

        $result = $processor->checkTake(1);
        $this->assertTrue($result);
    }

    public function testGetTargetShowUrl()
    {
        $processor = $this->_createClassroomProcessor();

        $result = $processor->getTargetShowUrl();
        $this->assertEquals('classroom_show', $result);
    }

    public function testAnnouncementNotificationIMDisabled()
    {
        $processor = $this->_createClassroomProcessor();

        $this->mockBiz('Classroom:ClassroomService', array(
            array(
                'functionName' => 'searchMemberCount',
                'returnValue' => 2,
            ),
            array(
                'functionName' => 'searchMembers',
                'returnValue' => array(array('userId' => 1), array('userId' => 2)),
            ),
        ));

        $this->mockBiz('System:SettingService', array(
            array(
                'functionName' => 'get',
                'returnValue' => array('enabled' => 0),
            ),
        ));

        $this->mockBiz('User:NotificationService', array(
            array(
                'functionName' => 'notify',
                'returnValue' => true,
            ),
        ));

        $result = $processor->announcementNotification(1, array('title' => 'announcement'), 'showurl');
        $this->assertTrue($result);
    }

    public function testAnnouncementNotification()
    {
        $processor = $this->_createClassroomProcessor();

        $this->mockBiz('Classroom:ClassroomService', array(
            array(
                'functionName' => 'searchMemberCount',
                'returnValue' => 2,
            ),
            array(
                'functionName' => 'searchMembers',
                'returnValue' => array(array('userId' => 1), array('userId' => 2)),
            ),
            array(
                'functionName' => 'getClassroom',
                'returnValue' => array('id' => 1, 'title' => 'classroom title'),
            ),
        ));

        $this->mockBiz('IM:ConversationService', array(
            array(
                'functionName' => 'getConversationByTarget',
                'returnValue' => array('no' => '1234567890'),
            ),
        ));

        $this->mockBiz('System:SettingService', array(
            array(
                'functionName' => 'get',
                'returnValue' => array('enabled' => 1),
            ),
        ));

        $this->mockBiz('User:NotificationService', array(
            array(
                'functionName' => 'notify',
                'returnValue' => true,
            ),
        ));

        $this->mockBiz('Queue:QueueService', array(
            array(
                'functionName' => 'pushJob',
                'returnValue' => array(),
            ),
        ));

        $result = $processor->announcementNotification(1, array('title' => 'announcement'), 'showurl');
        $this->assertTrue($result);
    }

    public function testIMEnabled()
    {
        $processor = $this->_createClassroomProcessor();

        $this->mockBiz('System:SettingService', array(
            array(
                'functionName' => 'get',
                'returnValue' => array('enabled' => 1),
            ),
        ));

        $result = $processor->isIMEnabled();

        $this->assertTrue($result);
    }

    public function testIMDisabled()
    {
        $processor = $this->_createClassroomProcessor();

        $this->mockBiz('System:SettingService', array(
            array(
                'functionName' => 'get',
                'returnValue' => array('enabled' => 0),
            ),
        ));

        $result = $processor->isIMEnabled();

        $this->assertFalse($result);
    }

    public function testTryManageObject()
    {
        $processor = $this->_createClassroomProcessor();

        $this->mockBiz('Classroom:ClassroomService', array(
            array(
                'functionName' => 'tryManageClassroom',
                'returnValue' => true,
            ),
            array(
                'functionName' => 'getClassroom',
                'returnValue' => array('id' => 1, 'title' => 'classroom content'),
            ),
        ));

        $classroom = $processor->tryManageObject(1);

        $this->assertEquals(1, $classroom['id']);
        $this->assertEquals('classroom content', $classroom['title']);
    }

    public function testGetTargetObject()
    {
        $processor = $this->_createClassroomProcessor();

        $this->mockBiz('Classroom:ClassroomService', array(
            array(
                'functionName' => 'getClassroom',
                'returnValue' => array('id' => 1),
            ),
        ));

        $result = $processor->getTargetObject(1);

        $this->assertEquals(1, $result['id']);
    }

    public function testGetActions()
    {
        $processor = $this->_createClassroomProcessor();

        $result = $processor->getActions('create');
        $this->assertEquals('AppBundle:Classroom/Announcement:create', $result);

        $result = $processor->getActions('edit');
        $this->assertEquals('AppBundle:Classroom/Announcement:edit', $result);

        $result = $processor->getActions('list');
        $this->assertEquals('AppBundle:Classroom/Announcement:list', $result);
    }

    private function _createClassroomProcessor()
    {
        $processor = new AnnouncementProcessorFactory($this->getBiz());

        return $processor->create('classroom');
    }
}
