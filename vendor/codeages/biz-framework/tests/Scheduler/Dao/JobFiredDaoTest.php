<?php

namespace Tests\Scheduler\Dao;

use Tests\IntegrationTestCase;

class JobFiredDaoTest extends IntegrationTestCase
{
    public function testDeleteWhenCreatedTimeBefore()
    {
        for ($i = 0; $i < 10; ++$i) {
            $this->getJobFiredDao()->create(
                array(
                    'job_id' => 1001001,
                    'fired_time' => time(),
                    'job_detail' => array('id' => 1001001),
                )
            );
        }

        $beforeTime = time() + 10;

        $jobsFired = $this->getJobFiredDao()->findByJobId(1001001);
        $this->assertEquals(10, count($jobsFired));

        $this->getJobFiredDao()->deleteWhenCreatedTimeBefore($beforeTime);

        $jobsFired = $this->getJobFiredDao()->findByJobId(1001001);
        $this->assertEquals(0, count($jobsFired));
    }

    protected function getJobFiredDao()
    {
        return $this->biz->dao('Scheduler:JobFiredDao');
    }
}
