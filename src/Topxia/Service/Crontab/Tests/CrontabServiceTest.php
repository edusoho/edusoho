<?php
namespace Topxia\Service\Course\Tests;

use Topxia\Service\Common\BaseTestCase;

class CrontabServiceTest extends BaseTestCase
{
    public function testCreateJob()
    {
        $job = array(
            'name'        => "TestJob",
            'cycle'       => 'once',
            'time'        => time() + 24 * 60 * 60,
            'jobClass'    => 'Topxia\\Service\\Test\\Test\\Test',
            'targetType'  => 'test',
            'targetId'    => 1,
            'creatorId'   => 1,
            'createdTime' => time(),
            'jobParams'   => ''
        );
        $newJob = $this->getCrontabService()->createJob($job);
        $this->assertEquals('TestJob', $newJob['name']);
    }

    public function testGetJob()
    {
        $newJob = $this->createJob();
        $jobGet = $this->getCrontabService()->getJob($newJob['id']);
        $this->assertEquals('once', $jobGet['cycle']);
        $this->assertEquals('测试定时任务', $jobGet['name']);
        $this->assertEquals('test', $jobGet['targetType']);
        $this->assertEquals('1', $jobGet['targetId']);
    }

    public function testSearchJobs()
    {
        $job1       = $this->createJob();
        $job2       = $this->createJob();
        $conditions = array(
            'creatorId' => 1
        );
        $result = $this->getCrontabService()->searchJobs($conditions, 'created', 0, 20);
        $this->assertEquals(2, count($result));
    }

    public function testSearchJobsCount()
    {
        $job1       = $this->createJob();
        $job2       = $this->createJob();
        $conditions = array(
            'creatorId' => 1
        );
        $result = $this->getCrontabService()->searchJobsCount($conditions);
        $this->assertEquals(2, $result);
    }

    public function testExecuteJob()
    {
    }

    public function testDeleteJob()
    {
        $job    = $this->createJob();
        $result = $this->getCrontabService()->deleteJob($job['id']);
        $this->assertEquals($result, $job['id']);
    }

    public function testScheduleJobs()
    {
    }

    public function testGetNextExcutedTime()
    {
    }

    public function testSetNextExcutedTime()
    {
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

    private function createJob()
    {
        $job = array(
            'name'        => '测试定时任务',
            'cycle'       => 'once',
            'time'        => time() + 24 * 60 * 60,
            'jobClass'    => 'Topxia\\Service\\Sms\\Job\\SmsSendOneDayJob',
            'targetType'  => 'test',
            'targetId'    => 1,
            'creatorId'   => 1,
            'createdTime' => time(),
            'jobParams'   => ''
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
