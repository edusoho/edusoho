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

    protected function createExampleFinishedJob()
    {
        $body = array('name' => 'example job');
        return new ExampleFinishedJob($body, array(), self::TEST_QUEUE);
    }

    protected function createExampleFailedJob()
    {
        $body = array('name' => 'example job');
        return new ExampleFailedJob($body, array(), self::TEST_QUEUE);
    }

}