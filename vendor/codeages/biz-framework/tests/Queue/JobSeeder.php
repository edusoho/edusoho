<?php

namespace Tests\Queue;

use Codeages\Biz\Framework\UnitTests\DatabaseSeeder;

class JobSeeder extends DatabaseSeeder
{
    public function run($isRun = true)
    {
        $rows = array(
            array(
                'id' => 1,
                'queue' => 'default',
                'class' => 'Tests\Fixtures\QueueJob\ExampleJob1',
                'body' => serialize(array('name' => 'job 1')),
                'attempts' => 0,
                'reserved_time' => 0,
                'available_time' => time(),
                'expired_time' => time() + 60,
                'created_time' => time(),
            ),
        );

        return $this->insertRows('biz_queue_job', $rows, $isRun);
    }
}
