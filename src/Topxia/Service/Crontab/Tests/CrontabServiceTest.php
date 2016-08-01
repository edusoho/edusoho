<?php
namespace Topxia\Service\Course\Tests;

use Topxia\Service\Common\BaseTestCase;

class CrontabServiceTest extends BaseTestCase
{
    public function testCreateJob()
    {
        $newJob = $this->createJob();
        $this->assertEquals('TestJob', $newJob['name']);
    }

    public function testGetJob()
    {
        $newJob = $this->createJob();
        $jobGet = $this->getCrontabService()->getJob($newJob['id']);
        $this->assertEquals('once', $jobGet['cycle']);
        $this->assertEquals('TestJob', $jobGet['name']);
        $this->assertEquals('test', $jobGet['targetType']);
        $this->assertEquals('1', $jobGet['targetId']);
    }

    public function testSearchJobs()
    {
        $job1       = $this->createJob();
        $job2       = $this->createJob();
        $user       = $this->getServiceKernel()->getCurrentUser();
        $conditions = array(
            'creatorId' => $user['id']
        );
        $results = $this->getCrontabService()->searchJobs($conditions, 'created', 0, 20);
        $this->assertEquals(2, count($results));
    }

    public function testSearchJobsCount()
    {
        $job1       = $this->createJob();
        $job2       = $this->createJob();
        $user       = $this->getServiceKernel()->getCurrentUser();
        $conditions = array(
            'creatorId' => $user['id']
        );
        $result = $this->getCrontabService()->searchJobsCount($conditions);
        $this->assertEquals(2, $result);
    }

    public function testExecuteJob()
    {
        $job = $this->createJob();
        //测试执行周期为一次的job
        $this->getCrontabService()->executeJob($job['id']);
        $getJob = $this->getCrontabService()->getJob($job['id']);
        $this->assertNull($getJob);

        //每小时执行一次
        $job = $this->createJob('everyhour');
        $this->getCrontabService()->executeJob($job['id']);
        $JobCount = $this->getCrontabService()->searchJobsCount(array('nextExcutedStartTime' => time())); //执行时间大于当前时间
        $this->assertEquals(1, $JobCount);

        $job = $this->createJob('everyhour');
        $this->getCrontabService()->executeJob($job['id']);
        $JobCount = $this->getCrontabService()->searchJobsCount(array('nextExcutedTime' => time())); //执行时间大于当前时间
        $this->assertEquals(0, $JobCount);

    }

    public function testDeleteJob()
    {
        $job    = $this->createJob();
        $result = $this->getCrontabService()->deleteJob($job['id']);
        $this->assertEquals($result, $job['id']);
    }

    public function testDeleteJobs()
    {
        $job    = $this->createJob();
        $result = $this->getCrontabService()->deleteJobs(1, 'test');
        $this->assertEquals($result, $job['id']);
    }

    public function testScheduleJobs()
    {
        $job = $this->createJob();
        //测试执行周期为一次的job
        $this->getCrontabService()->scheduleJobs();
        $getJob = $this->getCrontabService()->getJob($job['id']);
        $this->assertNull($getJob);
    }

    public function testGetNextExcutedTime()
    {
        $job = $this->createJob('everyhour');
        $this->getCrontabService()->executeJob($job['id']);
        $job             = $this->getCrontabService()->getJob($job['id']); //执行时间大于当前时间
        $nextExcutedTime = $this->getCrontabService()->getNextExcutedTime();

        $this->assertEquals($job['nextExcutedTime'], $nextExcutedTime);
    }

    public function testSetNextExcutedTime()
    {
        $expectedTime = time();
        $this->getCrontabService()->setNextExcutedTime($expectedTime);
        $nextExcutedTime = $this->getCrontabService()->getNextExcutedTime();

        $this->assertEquals(time(), $nextExcutedTime);
    }

    public function testFindJobByTargetTypeAndTargetId()
    {
        $newJob = $this->createJob();
        $result = $this->getCrontabService()->findJobByTargetTypeAndTargetId($newJob['targetType'], $newJob['targetId']);
        $this->assertEquals($newJob['targetId'], $result[0]['targetId']);
        $this->assertEquals($newJob['targetType'], $result[0]['targetType']);
    }

    public function testFindJobByNameAndTargetTypeAndTargetId()
    {
        $newJob = $this->createJob();
        $result = $this->getCrontabService()->findJobByNameAndTargetTypeAndTargetId($newJob['name'], $newJob['targetType'], $newJob['targetId']);
        $this->assertEquals($newJob['targetId'], $result['targetId']);
        $this->assertEquals($newJob['targetType'], $result['targetType']);
        $this->assertEquals($newJob['name'], $result['name']);
    }

    public function testUpdateJob()
    {
        $newJob = $this->createJob();
        $fields = array(
            'name' => 'newTest'
        );
        $updateJob = $this->getCrontabService()->updateJob($newJob['id'], $fields);
        $this->assertEquals('newTest', $updateJob['name']);
        $this->assertEquals($newJob['id'], $updateJob['id']);
    }

    private function createJob($cycle = 'once')
    {
        $job = array(
            'name'            => "TestJob",
            'cycle'           => $cycle,
            'nextExcutedTime' => time(), //方便执行的时候,可以处理当前的job
            'jobClass'        => 'Topxia\\Service\\Crontab\\Tests\\TestJob',
            'targetType'      => 'test',
            'targetId'        => 1,
            'creatorId'       => 1,
            'createdTime'     => time(),
            'jobParams'       => ''
        );
        $job = $this->getCrontabService()->createJob($job);
        return $job;
    }

    protected function getCrontabService()
    {
        return $this->getServiceKernel()->createService('Crontab.CrontabService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

}
