<?php

namespace Tests\Unit\Distributor\Dao\Impl;

use Biz\BaseTestCase;

class DistributorJobDataTest extends BaseTestCase
{
    public function testDeclares()
    {
        $this->assertArrayEquals(
            array(
                'timestamps' => array('createdTime', 'updatedTime'),
                'conditions' => array(
                    'jobType = :jobType',
                    'status in (:statusArr)',
                    'status = :status',
                ),
                'orderbys' => array(
                    'id',
                ),
                'serializes' => array(
                    'data' => 'json',
                ),
            ),
            $this->getDistributorJobDataDao()->declares()
        );
    }

    private function getDistributorJobDataDao()
    {
        return $this->createDao('Distributor:DistributorJobDataDao');
    }
}
