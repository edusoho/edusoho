<?php

namespace Tests\Unit\RewardPointProduct\Dao;

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
                'condition' => array('address' => '越源大厦'),
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ),
            array(
                'condition' => array('title' => '笔记本'),
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ),
        );

        $this->searchTestUtil($this->getDao(), $testConditions, $this->getCompareKeys());
    }

    public function testCount()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('userId' => 2));
        $expected[] = $this->mockDataObject(array('productId' => 2, 'userId' => 1));

        $res1 = $this->getDao()->count(array(), array(), 0, 10);
        $res2 = $this->getDao()->count(array('productId' => 2,'userId' => 1), array(), 0, 10);

        $this->assertEquals(3, $res1);
        $this->assertEquals(1, $res2);
    }

    public function testFindByProductId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('productId' => 2));
        $expected[] = $this->mockDataObject(array('productId' => 3));

        $res = $this->getDao()->findByProductId(2);

        $this->assertEquals($expected[1], $res);
    }

    public function testFindByUserId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('userId' => 2));
        $expected[] = $this->mockDataObject(array('userId' => 3));

        $res = $this->getDao()->findByUserId(2);

        $this->assertEquals($expected[1], $res);
    }

    protected function getDefaultMockFields()
    {
        return array(
            'sn' => '1010',
            'productId' => 1,
            'title' => '笔记本',
            'price' => 454,
            'userId' => 1,
            'telephone' => '16732147311',
            'email' => 'edusoho@howzhi.com',
            'address' => '越源大厦',
            'sendTime' => 1111111111,
            'status' => 'created',
        );
    }
}
