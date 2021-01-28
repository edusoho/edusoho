<?php

namespace Tests\Unit\WeChatNotification\Job;

use Biz\BaseTestCase;
use Biz\WeChatNotification\Job\LessonPublishNotificationJob;

class LessonPublishNotificationJobTest extends BaseTestCase
{
    public function testExecute()
    {
        $this->mockBiz('WeChat:WeChatService', array(
            array('functionName' => 'getTemplateId', 'returnValue' => 2),
            array('functionName' => 'findSubscribedUsersByUserIdsAndType', 'returnValue' => array(array('openId' => 123))),
        ));
        $this->mockBiz('Task:TaskService', array(
            array('functionName' => 'getTask', 'returnValue' => array('status' => 'published', 'courseId' => 3, 'type' => 'live', 'startTime' => time(), 'updatedTime' => time() + 86400)),
        ));
        $this->mockBiz('Course:CourseService', array(
            array('functionName' => 'getCourse', 'returnValue' => array('status' => 'published', 'courseSetId' => 4, 'id' => 5, 'locked' => 0, 'parentId' => 10)),
        ));
        $this->mockBiz('Course:CourseSetService', array(
            array('functionName' => 'getCourseSet', 'returnValue' => array('status' => 'published', 'parentId' => 1, 'title' => 'test CourseSet', 'type' => 'live')),
        ));
        $this->mockBiz('Course:MemberService', array(
            array('functionName' => 'searchMembers', 'returnValue' => array(array('userId' => 12))),
        ));
        $this->mockBiz('Classroom:ClassroomService', array(
            array('functionName' => 'getClassroomByCourseId', 'returnValue' => array('id' => 1)),
            array('functionName' => 'searchMembers', 'returnValue' => array(array('id' => 1))),
        ));
        $this->mockBiz('User:UserService', array(
            array('functionName' => 'getUser', 'returnValue' => array('nickname' => 'testName')),
        ));

        $job = new LessonPublishNotificationJob(array(), $this->biz);
        $job->args = array('key' => 1, 'taskId' => 1, 'url' => 'www.baidu.com');
        $result = $job->execute();

        $this->assertEmpty($result);
    }
}
