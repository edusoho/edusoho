<?php

namespace Tests\Unit\Plumber;

use Biz\BaseTestCase;
use Biz\Plumber\Dao\PlumberQueueDao;
use Biz\Plumber\Service\PlumberQueueService;
use Codeages\Plumber\Queue\Job;

class PlumberQueueServiceTest extends BaseTestCase
{
    public function testSearchQueue()
    {
        $queue1 = $this->createQueues();
        $queue2 = $this->createQueues('test worker2', 'test job id 2');

        $result = $this->getPlumberQueueService()->searchQueues();

        $this->assertCount(2, $result);
        $this->assertArrayEquals([$queue1, $queue2], $result);

        $result = $this->getPlumberQueueService()->searchQueues(['jobId' => $queue1['jobId']]);

        $this->assertCount(1, $result);
        $this->assertArrayEquals($queue1, $result[0]);
    }

    public function testCountQueues()
    {
        $queue1 = $this->createQueues();
        $queue2 = $this->createQueues('test worker2', 'test job id 2');

        $result = $this->getPlumberQueueService()->countQueues();

        $this->assertEquals(2, $result);

        $result = $this->getPlumberQueueService()->countQueues(['jobId' => $queue1['jobId']]);

        $this->assertEquals(1, $result);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     * @expectedExceptionMessage exception.common_parameter_missing
     */
    public function testCreateQueue_Exception_ParameterMissing()
    {
        $job = new Job();
        $job->setBody(json_encode(['test']));

        $this->getPlumberQueueService()->createQueue($job, 'acquired');
    }

    public function testCreateQueue()
    {
        $body = ['id' => 'test job id', 'worker' => 'test worker'];
        $job = new Job();
        $job->setBody(json_encode($body));
        $job->setPriority(500);

        $result = $this->getPlumberQueueService()->createQueue($job, 'acquired');
        $this->assertEquals('test worker', $result['worker']);
        $this->assertEquals('test job id', $result['jobId']);
        $this->assertEquals(500, $result['priority']);
        $this->assertEmpty($result['trace']);
        $this->assertArrayEquals($body, $result['body']);
    }

    /**
     * @expectedException \Biz\Plumber\PlumberException
     * @expectedExceptionMessage exception.plumber.not_found_queue
     */
    public function testUpdateQueueStatus_Exception_NotFound()
    {
        $queue = $this->createQueues();

        $this->getPlumberQueueService()->updateQueueStatus(1111111, 'executing');
    }

    public function testUpdateQueueStatus()
    {
        $queue = $this->createQueues();

        $result = $this->getPlumberQueueService()->updateQueueStatus($queue['id'], 'executing');
        $this->assertEquals($queue['status'], 'acquired');
        $this->assertEquals($result['status'], 'executing');
    }

    protected function createQueues($worker = 'test worker', $jobId = 'test job id', $body = ['id' => 1, 'message' => 'message'], $status = 'acquired')
    {
        $queue = [
            'worker' => $worker,
            'jobId' => $jobId,
            'body' => $body,
            'status' => $status,
            'priority' => 500,
            'trace' => '',
        ];

        return $this->getPlumberQueueDao()->create($queue);
    }

    /**
     * @return PlumberQueueService
     */
    protected function getPlumberQueueService()
    {
        return $this->createService('Plumber:PlumberQueueService');
    }

    /**
     * @return PlumberQueueDao
     */
    protected function getPlumberQueueDao()
    {
        return $this->createDao('Plumber:PlumberQueueDao');
    }
}
