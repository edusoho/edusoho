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
        $this->getActivityWatchLogDao()->create(array_merge($this->mockWatchLog(), array('updated_time' => time() - 3600)));
        $job = new AddActivityWatchToStatementJob();
        ReflectionUtils::setProperty($job, 'biz', $this->biz);
        $job->execute();
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
