<?php

namespace Tests\Cash\Dao;

use Tests\Base\BaseDaoTestCase;

class CashChangeDaoTest extends BaseDaoTestCase
{
    public function testGetByUserId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $res = $this->getDao()->getByUserId(1);
        $this->assertArrayEquals($expected[0], $res);
    }

    public function testWaveCashField()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $this->getDao()->WaveCashField(1, 1);
        $res = $this->getDao()->getByUserId(1);
        $this->assertEquals('2', $res['amount']);
    }

    protected function getDefaultMockfields()
    {
        return array(
            'userId' => 1,
            'amount' => 1
            );
    }
}
