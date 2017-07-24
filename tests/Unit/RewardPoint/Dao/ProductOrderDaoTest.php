<?php

namespace Tests\Unit\RewardPoint\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class ProductOrderDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject();

        $testConditions = array(
            array(
                'condition' => array('ids' => range(1, 3)),
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ),
            array(
                'condition' => array('status' => 'created'),
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ),
            array(
                'condition' => array('address' => 'testAddress'),
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ),
            array(
                'condition' => array('title' => 'book'),
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ),
        );

        $this->searchTestUtil($this->getDao(), $testConditions, $this->getCompareKeys());
    }

    public function testFindByProductId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject();
        $res = $this->getDao()->findByProductId(1);

        $this->assertEquals(array($expected[0], $expected[1], $expected[2]), $res);
    }

    public function testFindByUserId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject();

        $res = $this->getDao()->findByUserId(1);

        $this->assertEquals(array($expected[0], $expected[1], $expected[2]), $res);
    }

    protected function getDefaultMockFields()
    {
        return array(
            'sn' => '1010',
            'productId' => 1,
            'title' => 'book',
            'price' => 454,
            'userId' => 1,
            'telephone' => '16732147311',
            'email' => 'test@kz.com',
            'address' => 'testAddress',
            'sendTime' => 1111111111,
            'status' => 'created',
        );
    }
}
