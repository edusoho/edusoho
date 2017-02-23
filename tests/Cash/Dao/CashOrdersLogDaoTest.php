<?php

namespace Tests\Cash\Dao;

use Tests\Base\BaseDaoTestCase;

class CashOrdersLogDaoTest extends BaseDaoTestCase
{
    public function testFindByOrderId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('type' => 's'));
        $expected[] = $this->mockDataObject(array('type' => 'w'));
        $expected[] = $this->mockDataObject(array('type' => 'w'));
        $res = $this->getDao()->findByOrderId(1);
        $testFields = $this->getCompareKeys();
        foreach ($res as $key => $result) {
            $this->assertArrayEquals($expected[$key], $result, $testFields);
        }
    }

    protected function getDefaultMockfields()
    {
        return array(
            'orderId' => 1,
            'ip' => 123,
            'type' => 'a'
            );
    }
}
