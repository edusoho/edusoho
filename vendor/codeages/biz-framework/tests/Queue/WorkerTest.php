<?php

namespace Tests\Queue;

use Codeages\Biz\Framework\Queue\Worker;
use Codeages\Biz\Framework\Queue\JobFailer;
use Codeages\Biz\Framework\Queue\Driver\DatabaseQueue;
use Tests\Fixtures\QueueJob\ExampleFinishedJob;
use Tests\Fixtures\QueueJob\ExampleFailedJob;
use Tests\Fixtures\QueueJob\ExampleFailedRetryJob;

class WorkerTest extends QueueBaseTestCase
{
    public function testRun_FinishedJob()
    {
        $queueOptions = $this->getQueueOptions();
        $queue = new DatabaseQueue(self::TEST_QUEUE, $this->biz, $queueOptions);
        $body = array('name' => 'example job');
        $job = new ExampleFinishedJob($body);
        $queue->push($job);

        $failer = new JobFailer($this->biz->dao('Queue:FailedJobDao'));

        $options = array(
            'once' => true,
        );

        $worker = new Worker($queue, $failer, $this->createLock(), $this->biz['logger'], $options);
        $worker->runNextJob();

        $this->assertTrue($this->biz['logger.test_handler']->hasInfo('ExampleFinishedJob executed.'));
        $this->assertCount(0, $this->fetchAllFromDatabase($queueOptions['table'], array('queue' => self::TEST_QUEUE)));
    }

    public function testRun_FailedJob()
    {
        $queueOptions = $this->getQueueOptions();
        $queue = new DatabaseQueue(self::TEST_QUEUE, $this->biz, $queueOptions);
        $body = array('name' => 'example job');
        $job = new ExampleFailedJob($body);
        $queue->push($job);

        $failer = new JobFailer($this->biz->dao('Queue:FailedJobDao'));

        $options = array(
            'once' => true,
        );

        $worker = new Worker($queue, $failer, $this->createLock(), $this->biz['logger'], $options);
        $worker->runNextJob();

        $this->assertTrue($this->biz['logger.test_handler']->hasInfo('ExampleFailedJob executed.'));

        $this->assertCount(0, $this->fetchAllFromDatabase($queueOptions['table'], array('queue' => self::TEST_QUEUE)));
        $this->assertCount(1, $this->fetchAllFromDatabase('biz_queue_failed_job', array(
            'queue' => self::TEST_QUEUE,
            'reason' => 'ExampleFailedJob execute failed.',
        )));
    }

    public function testRun_FailedRetryJob()
    {
        $queueOptions = $this->getQueueOptions();
        $queue = new DatabaseQueue(self::TEST_QUEUE, $this->biz, $queueOptions);
        $body = array('name' => 'example job');
        $job = new ExampleFailedRetryJob($body);
        $queue->push($job);

        $failer = new JobFailer($this->biz->dao('Queue:FailedJobDao'));

        $options = array(
            'once' => true,
        );

        $worker = new Worker($queue, $failer, $this->createLock(), $this->biz['logger'], $options);
        $worker->runNextJob();

        $this->assertTrue($this->biz['logger.test_handler']->hasInfo('ExampleFailedRetryJob executed.'));

        $this->assertCount(0, $this->fetchAllFromDatabase($queueOptions['table'], array(
            'queue' => self::TEST_QUEUE,
            'executions' => 1,
            'reserved_time' => 0,
            'expired_time' => 0,
        )));
    }
}
