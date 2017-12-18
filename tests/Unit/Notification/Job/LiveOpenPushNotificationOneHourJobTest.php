<?php

namespace Tests\Unit\Notification\Job;

use Biz\BaseTestCase;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\Notification\Job\LiveOpenPushNotificationOneHourJob;

class LiveOpenPushNotificationOneHourJobTest extends BaseTestCase
{
    public function testExecute()
    {
        $this->mockBiz('OpenCourse:OpenCourseService', array(
            array(
                'functionName' => 'getLesson',
                'returnValue' => array('id' => 1, 'title' => 'test Title', 'type' => 'video', 'courseId' => 1),
            ),
            array(
                'functionName' => 'getCourse',
                'returnValue' => array('id' => 1, 'title' => 'test Title', 'smallPicture' => '/a/b/c.jpg'),
            ),
        ));

        $api = CloudAPIFactory::create('root');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('post')->times(1)->andReturn(array('success' => true));

        $job = new LiveOpenPushNotificationOneHourJob(array('args' => array('targetType' => 'liveOpenLesson', 'targetId' => 1)), $this->getBiz());
        $result = $job->execute();

        $this->assertNull($result);
    }
}
