<?php

namespace Tests\Unit\Activity\Service;

use Biz\BaseTestCase;
use Biz\Activity\Service\ActivityLearnLogService;

class ActivityLearnLogServiceTest extends BaseTestCase
{
    public function testCreateLog()
    {
        $activity = array('id' => 11, 'mediaType' => 'text');
        $data = array('lastTime' => 1111111, 'watchedTime' => 2222222, 'task' => array('id' => 1));
        $log1 = $this->getActivityLearnLogService()->createLog($activity, 'test', $data);

        $this->assertEquals(11, $log1['activityId']);

        $data = array('lastTime' => 1111111, 'watchedTime' => 2222222, 'taskId' => 22);
        $log2 = $this->getActivityLearnLogService()->createLog($activity, 'test', $data);

        $this->assertEquals(22, $log2['courseTaskId']);

        $data = array('lastTime' => 1111111, 'watchedTime' => 2222222);
        $log3 = $this->getActivityLearnLogService()->createLog($activity, 'test', $data);

        $this->assertEquals(0, $log3['courseTaskId']);
    }

    public function testGetMyRecentFinishLogByActivityId()
    {
        $activity = array('id' => 11, 'mediaType' => 'text');
        $data = array('lastTime' => 1111111, 'watchedTime' => 2222222);
        $log = $this->getActivityLearnLogService()->createLog($activity, 'finish', $data);
        $result = $this->getActivityLearnLogService()->getMyRecentFinishLogByActivityId(11);

        $this->assertEquals(11, $result[0]['activityId']);
    }

    public function testCalcLearnProcessByCourseIdAndUserId()
    {
        $this->mockBiz(
            'Activity:ActivityLearnLogDao',
            array(
                array(
                    'functionName' => 'countLearnedDaysByCourseIdAndUserId',
                    'returnValue' => 10,
                    'withParams' => array(24, 24),
                ),
            )
        );

        $result = $this->getActivityLearnLogService()->calcLearnProcessByCourseIdAndUserId(24, 24);

        $this->assertEquals(10, $result[0]);
    }

    public function testDeleteLearnLogsByActivityId()
    {
        $this->mockBiz(
            'Activity:ActivityLearnLogDao',
            array(
                array(
                    'functionName' => 'deleteByActivityId',
                    'returnValue' => array('id' => 111, 'event' => 'finish'),
                    'withParams' => array(33),
                ),
            )
        );

        $result = $this->getActivityLearnLogService()->deleteLearnLogsByActivityId(33);

        $this->assertEquals(array('id' => 111, 'event' => 'finish'), $result);
    }

    public function testGetLastestLearnLogByActivityIdAndUserId()
    {
        $this->mockBiz(
            'Activity:ActivityLearnLogDao',
            array(
                array(
                    'functionName' => 'getLastestByActivityIdAndUserId',
                    'returnValue' => array('id' => 111, 'event' => 'finish'),
                    'withParams' => array(33, 22),
                ),
            )
        );

        $result = $this->getActivityLearnLogService()->getLastestLearnLogByActivityIdAndUserId(33, 22);

        $this->assertEquals(array('id' => 111, 'event' => 'finish'), $result);
    }

    public function testSumLearnTimeGroupByUserId()
    {
        $time = time();
        $this->getActivityLearnLogDao()->create(array('userId' => 1, 'learnedTime' => 100, 'event' => 'doing', 'mediaType' => 'ppt'));
        $this->getActivityLearnLogDao()->create(array('userId' => 1, 'learnedTime' => 12, 'event' => 'start', 'mediaType' => 'ppt'));
        $this->getActivityLearnLogDao()->create(array('userId' => 2, 'learnedTime' => 11, 'event' => 'finish', 'mediaType' => 'ppt'));

        $result = $this->getActivityLearnLogService()->sumLearnTimeGroupByUserId(array());

        $this->assertEquals('112', $result[1]['learnedTime']);
        $this->assertEquals('11', $result[2]['learnedTime']);

        $result = $this->getActivityLearnLogService()->sumLearnTimeGroupByUserId(array('createdTime_GE' => $time + 10 * 3600));
        $this->assertEmpty($result);

        $result = $this->getActivityLearnLogService()->sumLearnTimeGroupByUserId(array('userIds' => array(3)));
        $this->assertEmpty($result);

        $result = $this->getActivityLearnLogService()->sumLearnTimeGroupByUserId(array('userIds' => array(2)));
        $this->assertEquals('11', $result[2]['learnedTime']);
    }

    protected function getActivityLearnLogService()
    {
        return $this->createService('Activity:ActivityLearnLogService');
    }

    protected function getActivityLearnLogDao()
    {
        return $this->createDao('Activity:ActivityLearnLogDao');
    }
}
