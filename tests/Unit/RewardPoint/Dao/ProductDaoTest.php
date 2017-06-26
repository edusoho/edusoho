<?php

namespace Tests\Unit\RewardPoint\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class ProductDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $expected = array();
        $expected[0] = $this->mockDataObject(array('status' => 'published'));
        $expected[1] = $this->mockDataObject(array('title' => 'RewardPointProductB'));
        $testCondition = array(
            array(
                'condition' => array(),
                'expectedResults' => $expected,
                'expectedCount' => 2,
            ),
            array(
                'condition' => array('status' => 'published'),
                'expectedResults' => array($expected[0]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('titleLike' => 'RewardPointProductB'),
                'expectedResults' => array($expected[1]),
                'expectedCount' => 1,
            ),
        );
        $this->searchTestUtil($this->getDao(), $testCondition, $this->getCompareKeys());
    }

    public function testFindByIds()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('title' => 'RewardPointProductB'));
        $expected[] = $this->mockDataObject();
        $res = $this->getDao()->findByIds(array(1, 2));
        $testFields = $this->getCompareKeys();
        foreach ($res as $key => $result) {
            $this->assertArrayEquals($expected[$key], $result, $testFields);
        }
    }

    protected function getDefaultMockFields()
    {
        return array(
            'title' => 'RewardPointProductA',
            'status' => '0',
            'createdTime' => 1,
        );
    }
}
