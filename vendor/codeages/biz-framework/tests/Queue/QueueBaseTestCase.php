<?php

namespace Tests\Queue;

use Tests\IntegrationTestCase;
use Tests\Fixtures\QueueJob\ExampleFinishedJob;
use Tests\Fixtures\QueueJob\ExampleFailedJob;

class QueueBaseTestCase extends IntegrationTestCase
{
    const TEST_QUEUE = 'test_queue';

    protected function getQueueOptions()
    {
        return  array(
            'table' => 'biz_queue_job',
        );
    }

    protected function createExampleFinishedJob(array $metadata = array())
    {
        $body = array('name' => 'example job');

        return new ExampleFinishedJob($body, $metadata);
    }

    protected function createExampleFailedJob(array $metadata = array())
    {
        $body = array('name' => 'example job');

        return new ExampleFailedJob($body, $metadata);
    }

    protected function createLock()
    {
        return $this->biz['lock.factory']->createLock('queue-for-phpunit');
    }
}
