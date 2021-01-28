<?php

namespace Tests;

use Codeages\Biz\Framework\Scheduler\Service\JobPool;
use Tests\Example\Job\ExampleJob;

class JobPoolTest extends IntegrationTestCase
{
    const NOT_EXIST_ID = 9999;

    public function testRun()
    {
        $job = new ExampleJob(array(
            'pool' => 'default',
        ));

        $pool = new JobPool($this->biz);
        $pool->execute($job);

        $poolDetail = $pool->getJobPool('default');
        $this->assertEquals(0, $poolDetail['num']);
        $this->assertEquals(10, $poolDetail['max_num']);
        $this->assertEquals(120, $poolDetail['timeout']);
    }
}
