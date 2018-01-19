<?php

namespace Tests\Unit\Sensitive\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class SensitiveDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('state' => 'banned', 'name' => 'ccc'));
        $expected[] = $this->mockDataObject(array('name' => 'aaabbb'));

        $testConditions = array(
            array(
                'condition' => array(),
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ),
            array(
                'condition' => array('id' => 1),
                'expectedResults' => array($expected[0]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('name' => 'bb'),
                'expectedResults' => array($expected[2]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('state' => 'banned'),
                'expectedResults' => array($expected[1]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('keyword' => 3, 'searchKeyWord' => 'id'),
                'expectedResults' => array($expected[2]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('keyword' => 'bb', 'searchKeyWord' => 'name'),
                'expectedResults' => array($expected[2]),
                'expectedCount' => 1,
            ),
        );

        $this->searchTestUtil($this->getDao(), $testConditions, $this->getCompareKeys());
    }

    public function testGetByName()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('name' => 'ccc'));
        $result = $this->getDao()->getByName('ccc');
        $this->assertArrayEquals($expected[1], $result, $this->getCompareKeys());
    }

    public function testFindAllKeywords()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('name' => 'ccc', 'createdTime' => 2000));
        $result = $this->getDao()->findAllKeywords();
        $this->assertArrayEquals($expected[1], $result[0], $this->getCompareKeys());
    }

    public function testFindByState()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('name' => 'ccc', 'state' => 'banned'));
        $result = $this->getDao()->findByState('banned');
        $this->assertArrayEquals($expected[1], $result[0], $this->getCompareKeys());
    }

    protected function getDefaultMockFields()
    {
        return array(
            'state' => 'replaced',
            'name' => 'aaa',
            'bannedNum' => 2,
            'createdTime' => 0,
        );
    }
}