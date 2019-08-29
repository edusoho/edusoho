<?php

namespace Tests\Unit\WeChatNotification\Job;

use Biz\BaseTestCase;
use Biz\WeChatNotification\Job\CourseRemindNotificationJob;

class CourseRemindNotificationJobTest extends BaseTestCase
{
    public function testExecute()
    {
        $this->mockBiz('WeChat:WeChatService', array(
            array('functionName' => 'getTemplateId', 'returnValue' => 2),
            array('functionName' => 'findSubscribedUsersByUserIdsAndType', 'returnValue' => array(array('openId' => 123))),
            array('functionName' => 'findAllBindUserIds', 'returnValue' => array(array('openId' => 123, 'userId' => 1))),
        ));
        $this->mockBiz('Course:MemberService', array(
            array('functionName' => 'findLastLearnTimeRecordStudents', 'returnValue' => array(array('userId' => 1, 'courseId' => 1, 'learnedCompulsoryTaskNum' => 1))),
        ));

        $job = new CourseRemindNotificationJob(array(), $this->biz);
        $job->args = array('key' => 1, 'usl' => 'xxxx', 'sendTime' => '00:00', 'sendDays' => array(''));
        $result = $job->execute();

        $this->assertEmpty($result);
    }
}
