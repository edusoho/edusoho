<?php

namespace Tests\Queue\Driver;

use Codeages\Biz\Framework\Queue\Driver\DatabaseQueue;
use Tests\Fixtures\QueueJob\ExampleFinishedJob;
use Tests\Queue\QueueBaseTestCase;

class DatabaseQueueTest extends QueueBaseTestCase
{
    public function testPush_ExampleJobWithDefaultMetadata()
    {
        $queueOptions = $this->getQueueOptions();
        $queue = new DatabaseQueue(self::TEST_QUEUE, $this->biz, $queueOptions);

        $job = $this->createExampleFinishedJob();
        $startTime = time();
        $queue->push($job);
        $endTime = time();

        $this->assertGreaterThan(0, $job->getId());

        $savedJob = $this->fetchFromDatabase($queueOptions['table'], array(
            'id' => $job->getId(),
        ));

        $this->assertEquals($queue->getName(), $savedJob['queue']);
        $this->assertEquals(get_class($job), $savedJob['class']);
        $this->assertEquals(ExampleFinishedJob::DEFAULT_TIMEOUT, $savedJob['timeout']);
        $this->assertEquals(ExampleFinishedJob::DEFAULT_PRIORITY, $savedJob['priority']);
        $this->assertGreaterThanOrEqual($startTime, $savedJob['available_time']);
        $this->assertLessThanOrEqual($endTime, $savedJob['available_time']);
    }

    public function testPush_ExampleJobWithMetadata()
    {
        $queueOptions = $this->getQueueOptions();
        $queue = new DatabaseQueue(self::TEST_QUEUE, $this->biz, $queueOptions);

        $job = $this->createExampleFinishedJob(array(
            'timeout' => 99,
            'priority' => ExampleFinishedJob::HIGHEST_PRIORITY,
            'delay' => 10,
        ));
        $startTime = time();
        $queue->push($job);
        $endTime = time();

        $this->assertGreaterThan(0, $job->getId());

        $savedJob = $this->fetchFromDatabase($queueOptions['table'], array(
            'id' => $job->getId(),
        ));

        $this->assertEquals(99, $savedJob['timeout']);
        $this->assertEquals(ExampleFinishedJob::HIGHEST_PRIORITY, $savedJob['priority']);
        $this->assertGreaterThanOrEqual($startTime + 10, $savedJob['available_time']);
        $this->assertLessThanOrEqual($endTime + 10, $savedJob['available_time']);
    }

    public function testPop_OnEmptyQueue()
    {
        $queueOptions = $this->getQueueOptions();
        $queue = new DatabaseQueue(self::TEST_QUEUE, $this->biz, $queueOptions);

        $popedJob = $queue->pop();

        $this->assertNull($popedJob);
    }

    public function testPop_OnHasJobQueue()
    {
        $queueOptions = $this->getQueueOptions();
        $queue = new DatabaseQueue(self::TEST_QUEUE, $this->biz, $queueOptions);

        $job = $this->createExampleFinishedJob();

        $queue->push($job);
        $popedJob = $queue->pop();

        $this->assertEquals($job->getBody(), $popedJob->getBody());
        $this->assertEquals($job->getId(), $popedJob->getId());
    }

    public function testPop_OnNotAvaliableJobQueue()
    {
        $queueOptions = $this->getQueueOptions();
        $queue = new DatabaseQueue(self::TEST_QUEUE, $this->biz, $queueOptions);

        $job = $this->createExampleFinishedJob(array(
            'delay' => 10,
        ));

        $queue->push($job);
        $popedJob = $queue->pop();

        $this->assertNull($popedJob);
    }

    public function testPop_OnExecutingJobQueue()
    {
        $queueOptions = $this->getQueueOptions();
        $queue = new DatabaseQueue(self::TEST_QUEUE, $this->biz, $queueOptions);

        $job = $this->createExampleFinishedJob();

        $queue->push($job);
        $queue->pop();
        $popedJob = $queue->pop();

        $this->assertNull($popedJob);
    }

    public function testPop_OnTimeoutJobQueue()
    {
        $queueOptions = $this->getQueueOptions();
        $queue = new DatabaseQueue(self::TEST_QUEUE, $this->biz, $queueOptions);

        $job = $this->createExampleFinishedJob(array('timeout' => 0));

        $queue->push($job);
        $queue->pop();
        $popedJob = $queue->pop();

        $this->assertEquals($job->getBody(), $popedJob->getBody());
        $this->assertEquals($job->getId(), $popedJob->getId());
    }

    public function testDelete()
    {
        $queueOptions = $this->getQueueOptions();
        $queue = new DatabaseQueue(self::TEST_QUEUE, $this->biz, $queueOptions);

        $body = array('name' => 'example job');
        $job = new ExampleFinishedJob($body);
        $queue->push($job);

        $queue->delete($job);

        $this->assertEmpty(
            $this->fetchAllFromDatabase($queueOptions['table'], array('queue' => self::TEST_QUEUE))
        );
    }

    public function testRelease()
    {
        $queueOptions = $this->getQueueOptions();
        $queue = new DatabaseQueue(self::TEST_QUEUE, $this->biz, $queueOptions);

        $body = array('name' => 'example job');
        $job = new ExampleFinishedJob($body);
        $queue->push($job);

        $job = $queue->pop();
        $queue->release($job);

        $savedJob = $this->fetchFromDatabase($queueOptions['table'], array(
            'id' => $job->getId(),
        ));

        $this->assertEquals(self::TEST_QUEUE, $savedJob['queue']);
        $this->assertEquals(1, $savedJob['executions']);
        $this->assertEquals(0, $savedJob['reserved_time']);
        $this->assertEquals(0, $savedJob['expired_time']);
    }
}
