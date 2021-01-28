<?php

namespace Tests\Unit\Card\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class CardDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('cardId' => '1', 'cardType' => 'b'));
        $expected[] = $this->mockDataObject(array('cardId' => '2', 'deadline' => 1));
        $expected[] = $this->mockDataObject(array('cardId' => '3', 'status' => 'used'));
        $expected[] = $this->mockDataObject(array('cardId' => '4', 'userId' => 2));
        $expected[] = $this->mockDataObject(array('cardId' => '5', 'useTime' => 2));

        $testCondition = array(
            array(
                'condition' => array(),
                'expectedResults' => $expected,
                'expectedCount' => 5,
                ),
            array(
                'condition' => array('cardType' => 'b'),
                'expectedResults' => array($expected[0]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('deadline' => 1),
                'expectedResults' => array($expected[1]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('status' => 'used'),
                'expectedResults' => array($expected[2]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('userId' => 2),
                'expectedResults' => array($expected[3]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('userIds' => array(2)),
                'expectedResults' => array($expected[3]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('startDateTime' => 2, 'endDateTime' => 3),
                'expectedResults' => array($expected[4]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('reciveStartTime' => 0, 'reciveEndTime' => PHP_INT_MAX),
                'expectedResults' => $expected,
                'expectedCount' => 5,
                ),
            );
        $this->searchTestUtil($this->getDao(), $testCondition, $this->getCompareKeys());
    }

    public function testGetByCardId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('cardId' => '1', 'cardType' => 'b'));
        $res = $this->getDao()->getByCardId('1');
        $this->assertArrayEquals($expected[0], $res, $this->getCompareKeys());
    }

    public function testGetByUserId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('cardId' => '1', 'userId' => 1));
        $res = $this->getDao()->getByUserId(1);
        $this->assertArrayEquals($expected[0], $res, $this->getCompareKeys());
    }

    public function testUpdateByCardIdAndCardType()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('userId' => 1));
        $fields = array('userId' => 1);
        $res = $this->getDao()->updateByCardIdAndCardType('1', 'a', $fields);
        $this->assertArrayEquals($expected[1], $res, $this->getCompareKeys());
    }

    public function testGetByCardIdAndCardType()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $res = $this->getDao()->getByCardIdAndCardType('1', 'a');
        $this->assertArrayEquals($expected[0], $res, $this->getCompareKeys());
    }

    public function testFindByUserIdAndCardType()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('cardId' => '1'));
        $expected[] = $this->mockDataObject(array('cardId' => '2'));
        $res = $this->getDao()->findByUserIdAndCardType(1, 'a');
        $this->assertArrayEquals($expected[0], $res[0], $this->getCompareKeys());
        $this->assertArrayEquals($expected[1], $res[1], $this->getCompareKeys());
    }

    public function testFindByUserIdAndCardTypeAndStatus()
    {
        $card = $this->mockDataObject(array('cardId' => '2'));
        $result = $this->getDao()->findByUserIdAndCardTypeAndStatus(1, 'a', 'receive');

        $this->assertArrayEquals($card, $result[0], $this->getCompareKeys());
    }

    public function testFindByCardIds()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('cardId' => '1'));
        $expected[] = $this->mockDataObject(array('cardId' => '2'));
        $res = $this->getDao()->findByCardIds(array(1, 2));
        $this->assertArrayEquals($expected[0], $res[0], $this->getCompareKeys());
        $this->assertArrayEquals($expected[1], $res[1], $this->getCompareKeys());
    }

    protected function getDefaultMockfields()
    {
        return array(
            'cardId' => '1',
            'cardType' => 'a',
            'deadline' => 0,
            'status' => 'receive',
            'useTime' => 0,
            'userId' => 1,
            );
    }
}
