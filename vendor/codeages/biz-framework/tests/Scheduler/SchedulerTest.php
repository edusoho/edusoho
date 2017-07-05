<?php

namespace Tests;


class SchedulerTest extends IntegrationTestCase
{
    /**
     * @expectedException \Exception
     */
    public function testCreateJobWithoutName()
    {
        $job = array(
            'source' => 'MAIN',
            'class' => 'Tests\\Example\\Job\\ExampleJob',
            'expression' => '0 17 * * *',
            'args' => array('courseId'=>1),
            'priority' => 100,
            'misfire_threshold' => 3000,
            'misfire_policy' => 'missed',
        );

        $this->getSchedulerService()->register($job);
    }

    /**
     * @expectedException \Exception
     */
    public function testCreateJobWithoutExpression()
    {
        $job = array(
            'name' => 'test',
            'source' => 'MAIN',
            'class' => 'Tests\\Example\\Job\\ExampleJob',
            'args' => array('courseId'=>1),
            'priority' => 100,
            'misfire_threshold' => 3000,
            'misfire_policy' => 'missed',
        );

        $this->getSchedulerService()->register($job);
    }

    /**
     * @expectedException \Exception
     */
    public function testCreateJobWithoutClass()
    {
        $job = array(
            'name' => 'test',
            'expression' => '0 17 * * *',
            'source' => 'MAIN',
            'args' => array('courseId'=>1),
            'priority' => 100,
            'misfire_threshold' => 3000,
            'misfire_policy' => 'missed',
        );

        $this->getSchedulerService()->register($job);
    }

    public function testCreateJob()
    {
        $job = array(
            'name' => 'test',
            'source' => 'MAIN',
            'expression' => '0 17 * * *',
//            'nextFireTime' => time()-1,
            'class' => 'Tests\\Example\\Job\\ExampleJob',
            'args' => array('courseId'=>1),
            'priority' => 100,
            'misfire_threshold' => 3000,
            'misfire_policy' => 'missed',
        );

        $savedJob = $this->getSchedulerService()->register($job);

        $this->asserts($job, $savedJob);
        $this->assertNotEmpty($savedJob['next_fire_time']);

        $logs = $this->getSchedulerService()->searchJobLogs(array(), array(), 0, 1);

        $excepted = array(
            'name' => 'test',
            'source' => 'MAIN',
            'class' => 'Tests\\Example\\Job\\ExampleJob',
            'args' => array('courseId'=>1),
            'status' => 'created',
        );
        foreach ($logs as $log) {
            $this->asserts($excepted, $log);
        }
    }

    public function testAfterNowRun()
    {
        $this->testCreateJob();
        $this->getSchedulerService()->execute();

        $time = time()+10;

        $job = array(
            'name' => 'test2',
            'source' => 'MAIN',
            'expression' => $time,
            'class' => 'Tests\\Example\\Job\\ExampleJob',
            'args' => array('courseId'=>1),
            'priority' => 100,
            'misfire_threshold' => 3000,
            'misfire_policy' => 'executing',
        );

        $savedJob = $this->getSchedulerService()->register($job);


        $this->getSchedulerService()->execute();
        $this->assertEquals($time-$time%60, $savedJob['next_fire_time']);

        $this->asserts($job, $savedJob);

        $jobFireds = $this->getSchedulerService()->findJobFiredsByJobId($savedJob['id']);
        $this->assertNotEmpty($jobFireds[0]);

        $jobFired = $jobFireds[0];
        $this->assertEquals('success', $jobFired['status']);

        $savedJob = $this->getJobDao()->get($savedJob['id']);
        $this->assertEquals(1, $savedJob['deleted']);
        $this->assertNotEmpty($savedJob['deleted_time']);
    }

    public function testBeforeNowRun()
    {
        $this->testCreateJob();
        $this->getSchedulerService()->execute();

        $time = time()-50000;

        $job = array(
            'name' => 'test2',
            'source' => 'MAIN',
            'expression' => $time,
            'class' => 'Tests\\Example\\Job\\ExampleJob',
            'args' => array('courseId'=>1),
            'priority' => 100,
            'misfire_threshold' => 3000,
            'misfire_policy' => 'executing',
        );

        $savedJob = $this->getSchedulerService()->register($job);


        $this->getSchedulerService()->execute();
        $this->assertEquals($time-$time%60, $savedJob['next_fire_time']);

        $this->asserts($job, $savedJob);

        $jobFireds = $this->getSchedulerService()->findJobFiredsByJobId($savedJob['id']);
        $this->assertNotEmpty($jobFireds[0]);

        $jobFired = $jobFireds[0];
        $this->assertEquals('success', $jobFired['status']);

        $savedJob = $this->getJobDao()->get($savedJob['id']);
        $this->assertEquals(1, $savedJob['deleted']);
        $this->assertNotEmpty($savedJob['deleted_time']);
    }

    public function testDeleteJobByName()
    {
        $job = array(
            'name' => 'test',
            'source' => 'MAIN',
            'expression' => '0 17 * * *',
//            'nextFireTime' => time()-1,
            'class' => 'Tests\\Example\\Job\\ExampleJob',
            'args' => array('courseId'=>1),
            'priority' => 100,
            'misfire_threshold' => 3000,
            'misfire_policy' => 'missed',
        );

        $savedJob = $this->getSchedulerService()->register($job);
        $this->getSchedulerService()->deleteJobByName('test');
        $savedJob = $this->getJobDao()->get($savedJob['id']);

        $this->assertEquals(1, $savedJob['deleted']);
        $this->assertNotEmpty($savedJob['deleted_time']);
    }


    protected function asserts($excepted, $acturel)
    {
        $keys = array_keys($excepted);
        foreach ($keys as $key) {
            if ('expression' == $key) {
                continue;
            }
            $this->assertEquals($excepted[$key], $acturel[$key]);
        }
    }

    protected function getJobDao()
    {
        return $this->biz->dao('Scheduler:JobDao');
    }

    protected function getSchedulerService()
    {
        return $this->biz->service('Scheduler:SchedulerService');
    }
}