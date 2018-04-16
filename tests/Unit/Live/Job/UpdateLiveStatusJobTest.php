<?php

namespace Tests\Unit\Live;

use Biz\BaseTestCase;
use Biz\Live\Job\UpdateLiveStatusJob;
use AppBundle\Common\ReflectionUtils;
use Biz\Util\EdusohoLiveClient;
use Mockery;

class UpdateLiveStatusJobTest extends BaseTestCase
{
    public function testExcute()
    {
        $activityService = $this->mockBiz('Activity:ActivityService', array(
            array(
                'functionName' => 'findFinishedLivesWithinTwoHours',
                'returnValue' => array(array('id' => 1, 'mediaId' => 1), array('id' => 2, 'mediaId' => 2)),
                'times' => 1,
            ),
        ));

        $liveActivityService = $this->mockBiz('Activity:LiveActivityService', array(
            array(
                'functionName' => 'search',
                'returnValue' => array(array('id' => 1, 'liveId' => 10, 'liveProvider' => 8), array('id' => 2, 'liveId' => 11, 'liveProvider' => 8)),
                'times' => 1,
            ),
            array(
                'functionName' => 'updateLiveStatus',
                'returnValue' => array(),
                'times' => 1,
            ),
        ));

        $openCourseService = $this->mockBiz('OpenCourse:OpenCourseService', array(
            array(
                'functionName' => 'findFinishedLivesWithinTwoHours',
                'returnValue' => array(array('id' => 1, 'mediaId' => 22, 'liveProvider' => 9), array('id' => 2, 'mediaId' => 23, 'liveProvider' => 9)),
                'times' => 1,
            ),
            array(
                'functionName' => 'updateLiveStatus',
                'returnValue' => array(),
                'times' => 1,
            ),
        ));

        $return = array(10 => 'closed', 11 => 'live', 22 => 'closed', 23 => 'live');
        $cloudLive = new EdusohoLiveClient();
        $mockObject = Mockery::mock($cloudLive);
        $mockObject->shouldReceive('checkLiveStatus')->times(1)->andReturn($return);

        $job = new UpdateLiveStatusJob(array(), $this->biz);
        ReflectionUtils::setProperty($job, 'liveApi', $mockObject);
        $result = $job->execute();
        $this->assertNull($result);

        $activityService->shouldHaveReceived('findFinishedLivesWithinTwoHours')->times(1);
        $liveActivityService->shouldHaveReceived('search')->times(1);
        $liveActivityService->shouldHaveReceived('updateLiveStatus')->times(2);
        $openCourseService->shouldHaveReceived('findFinishedLivesWithinTwoHours')->times(1);
        $openCourseService->shouldHaveReceived('updateLiveStatus')->times(2);
    }

    public function testFindLivesByActivity()
    {
        $job = new UpdateLiveStatusJob(array(), $this->biz);
        $result = ReflectionUtils::invokeMethod($job, 'findLivesByActivity');
        $this->assertEmpty($result);

        $activityService = $this->mockBiz('Activity:ActivityService', array(
            array(
                'functionName' => 'findFinishedLivesWithinTwoHours',
                'returnValue' => array(array('id' => 1, 'mediaId' => 1), array('id' => 2, 'mediaId' => 2)),
                'times' => 1,
            ),
        ));

        $liveActivityService = $this->mockBiz('Activity:LiveActivityService', array(
            array(
                'functionName' => 'search',
                'returnValue' => array(),
                'times' => 1,
            ),
        ));

        $job = new UpdateLiveStatusJob(array(), $this->biz);
        $result = ReflectionUtils::invokeMethod($job, 'findLivesByActivity');
        $this->assertEmpty($result);

        $liveActivityService = $this->mockBiz('Activity:LiveActivityService', array(
            array(
                'functionName' => 'search',
                'returnValue' => array(array('id' => 1, 'liveId' => 10, 'liveProvider' => 8), array('id' => 2, 'liveId' => 11, 'liveProvider' => 8)),
                'times' => 1,
            ),
        ));

        $job = new UpdateLiveStatusJob(array(), $this->biz);
        $result = ReflectionUtils::invokeMethod($job, 'findLivesByActivity');
        $this->assertEquals(2, count($result));
        $this->assertArrayHasKey('id', $result[0]);
        $this->assertArrayHasKey('type', $result[0]);
        $this->assertArrayHasKey('liveId', $result[0]);
        $this->assertArrayHasKey('liveProvider', $result[0]);
        $this->assertEquals('course', $result[0]['type']);
    }

    public function testFindLivesByOpenCourseLesson()
    {
        $job = new UpdateLiveStatusJob(array(), $this->biz);
        $result = ReflectionUtils::invokeMethod($job, 'findLivesByOpenCourseLesson');
        $this->assertEmpty($result);

        $openCourseService = $this->mockBiz('OpenCourse:OpenCourseService', array(
            array(
                'functionName' => 'findFinishedLivesWithinTwoHours',
                'returnValue' => array(array('id' => 1, 'mediaId' => 22, 'liveProvider' => 9), array('id' => 2, 'mediaId' => 23, 'liveProvider' => 9)),
                'times' => 1,
            ),
        ));

        $job = new UpdateLiveStatusJob(array(), $this->biz);
        $result = ReflectionUtils::invokeMethod($job, 'findLivesByOpenCourseLesson');

        $this->assertEquals(2, count($result));
        $this->assertArrayHasKey('id', $result[0]);
        $this->assertArrayHasKey('type', $result[0]);
        $this->assertArrayHasKey('liveId', $result[0]);
        $this->assertArrayHasKey('liveProvider', $result[0]);
        $this->assertEquals('openCourse', $result[0]['type']);
    }

    public function testCheckLiveStatusFromCloud()
    {
        $lives = array();
        $job = new UpdateLiveStatusJob(array(), $this->biz);
        $result = ReflectionUtils::invokeMethod($job, 'checkLiveStatusFromCloud', array($lives));
        $this->assertEmpty($result);

        $lives = array(
            array('id' => 1, 'liveId' => 22, 'liveProvider' => 9),
            array('id' => 2, 'liveId' => 23, 'liveProvider' => 9),
        );

        $return = array(10 => 'closed', 11 => 'live', 22 => 'closed', 23 => 'live');
        $cloudLive = new EdusohoLiveClient();
        $mockObject = Mockery::mock($cloudLive);
        $mockObject->shouldReceive('checkLiveStatus')->times(1)->andReturn($return);

        $job = new UpdateLiveStatusJob(array(), $this->biz);
        ReflectionUtils::setProperty($job, 'liveApi', $mockObject);
        $result = ReflectionUtils::invokeMethod($job, 'checkLiveStatusFromCloud', array($lives));

        $this->assertArrayEquals($return, $result);
    }

    public function testFormatLives()
    {
        $lives = array();
        $job = new UpdateLiveStatusJob(array(), $this->biz);
        $result = ReflectionUtils::invokeMethod($job, 'formatLives', array($lives));
        $this->assertEmpty($result);

        $lives = array(
            array('id' => 1, 'liveId' => 22, 'liveProvider' => 9),
            array('id' => 2, 'liveId' => 23, 'liveProvider' => 9),
        );
        $job = new UpdateLiveStatusJob(array(), $this->biz);
        $result = ReflectionUtils::invokeMethod($job, 'formatLives', array($lives));

        $this->assertEquals(1, count($result));
        $this->assertArrayHasKey(9, $result);
        $this->assertEquals(2, count($result[9]));
    }

    public function testUpdateLivesStatus()
    {
        $job = new UpdateLiveStatusJob(array(), $this->biz);
        $result = ReflectionUtils::invokeMethod($job, 'updateLivesStatus', array(array(), array()));
        $this->assertNull($result);

        $lives = array(
            array('id' => 1, 'liveId' => 22, 'liveProvider' => 9, 'type' => 'course'),
            array('id' => 2, 'liveId' => 23, 'liveProvider' => 9, 'type' => 'openCourse'),
            array('id' => 3, 'liveId' => 24, 'liveProvider' => 9, 'type' => 'course'),
        );
        $livesStatus = array(22 => 'closed', 23 => 'unstart');

        $liveActivityService = $this->mockBiz('Activity:LiveActivityService', array(
            array(
                'functionName' => 'updateLiveStatus',
                'returnValue' => array(),
                'times' => 1,
            ),
        ));

        $openCourseService = $this->mockBiz('OpenCourse:OpenCourseService', array(
            array(
                'functionName' => 'updateLiveStatus',
                'returnValue' => array(),
                'times' => 1,
            ),
        ));

        $job = new UpdateLiveStatusJob(array(), $this->biz);
        $result = ReflectionUtils::invokeMethod($job, 'updateLivesStatus', array($lives, $livesStatus));

        $this->assertNull($result);
    }

    public function createLiveApi()
    {
        $job = new UpdateLiveStatusJob(array(), $this->biz);
        $client = ReflectionUtils::invokeMethod($job, 'createLiveApi');

        $this->assertInstanceOf('Biz\Util\EdusohoLiveClient', $client);
    }
}
