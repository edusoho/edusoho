<?php

namespace Tests\Scheduler\Dao;

use Tests\IntegrationTestCase;

class JobFiredDaoTest extends IntegrationTestCase
{
    public function testDeleteUnacquiredBeforeCreatedTime()
    {
        for ($i = 0; $i < 10; ++$i) {
            if (0 == $i) {
                $status = 'acquired';
            } else {
                $status = 'pool_full';
            }

            $this->getJobFiredDao()->create(
                array(
                    'job_id' => 1001001,
                    'fired_time' => time(),
                    'job_detail' => array('id' => 1001001),
                    'status' => $status,
                )
            );
        }

        $beforeTime = time() + 10;

        $jobsFired = $this->getJobFiredDao()->findByJobId(1001001);
        $this->assertEquals(10, count($jobsFired));

        $this->getJobFiredDao()->deleteUnacquiredBeforeCreatedTime($beforeTime);

        $jobsFired = $this->getJobFiredDao()->findByJobId(1001001);
        $this->assertEquals(1, count($jobsFired));
    }

    protected function getJobFiredDao()
    {
        return $this->biz->dao('Scheduler:JobFiredDao');
    }
}
