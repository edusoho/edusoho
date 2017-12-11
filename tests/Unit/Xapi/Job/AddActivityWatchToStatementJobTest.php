<?php

namespace Tests\Unit\Xapi\Job;

use AppBundle\Common\ReflectionUtils;
use Biz\BaseTestCase;
use Biz\Xapi\Dao\ActivityWatchLogDao;
use Biz\Xapi\Job\AddActivityWatchToStatementJob;

class AddActivityWatchToStatementJobTest extends BaseTestCase
{
    public function testExecute()
    {
        $this->mockBiz('Xapi:XapiService', array(
            array('functionName' => 'searchWatchLogs', 'returnValue' => array(
                array('id' => 1, 'user_id' => 1, 'updated_time' => time(), 'activity_id' => 1),
                array('id' => 2, 'user_id' => 2, 'updated_time' => time(), 'activity_id' => 2),
                array('id' => 3, 'user_id' => 3, 'updated_time' => time(), 'activity_id' => 3),
            )),
            array('functionName' => 'createStatement', 'returnValue' => true),
            array('functionName' => 'updateWatchLog', 'returnValue' => true),
        ));

        $this->mockBiz('Activity:ActivityService', array(
            array('functionName' => 'getActivity', 'returnValue' => array('id' => 1, 'mediaType' => 'listen')),
        ));
        $this->getActivityWatchLogDao()->create(array_merge($this->mockWatchLog(), array('updated_time' => time() - 3600)));
        $job = new AddActivityWatchToStatementJob();
        ReflectionUtils::setProperty($job, 'biz', $this->biz);
        $this->assertNull($job->execute());
    }

    protected function mockWatchLog()
    {
        return array(
            'user_id' => 1,
            'activity_id' => 1,
            'course_id' => 1,
            'task_id' => 1,
            'watched_time' => 100,
            'is_push' => 0,
        );
    }

    /**
     * @return ActivityWatchLogDao
     */
    protected function getActivityWatchLogDao()
    {
        return $this->createDao('Xapi:ActivityWatchLogDao');
    }
}
