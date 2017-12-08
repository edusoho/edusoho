<?php

namespace Tests\Unit\Content\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class CommentDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $expected = $this->_createDatas();

        $testCondition = array(
            array(
                'condition' => array(),
                'expectedResults' => $expected,
                'expectedCount' => 4,
            ),
            array(
                'condition' => array('objectType' => 'classroom'),
                'expectedResults' => array($expected[1]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('objectId' => 1),
                'expectedResults' => array($expected[0], $expected[2]),
                'expectedCount' => 2,
            ),
            array(
                'condition' => array('objectType' => 'course', 'objectId' => 2),
                'expectedResults' => array($expected[3]),
                'expectedCount' => 1,
            ),
        );

        $this->searchTestUtil($this->getDao(), $testCondition, $this->getCompareKeys());
    }

    public function testFindByObjectTypeAndObjectId()
    {
        $expected = $this->_createDatas();

        $result = $this->getDao()->findByObjectTypeAndObjectId('course', 1, 0, 10);
        $this->assertEquals(2, count($result));

        $result = $this->getDao()->findByObjectTypeAndObjectId('course', 2, 0, 10);
        $this->assertEquals(1, count($result));
        $this->assertArrayEquals($expected[3], $result[0]);
    }

    public function testFindByObjectType()
    {
        $expected = $this->_createDatas();
        $result = $this->getDao()->findByObjectType('course', 0, 10);
        $this->assertEquals(3, count($result));

        $result = $this->getDao()->findByObjectType('classroom', 0, 10);
        $this->assertEquals(1, count($result));
        $this->assertArrayEquals($expected[1], $result[0]);
    }

    public function testCountByObjectType()
    {
        $expected = $this->_createDatas();

        $result = $this->getDao()->countByObjectType('course');
        $this->assertEquals(3, $result);

        $result = $this->getDao()->countByObjectType('classroom');
        $this->assertEquals(1, $result);
    }

    private function _createDatas()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('objectType' => 'course', 'objectId' => 1, 'userId' => 1, 'content' => 'content1'));
        $expected[] = $this->mockDataObject(array('objectType' => 'classroom', 'objectId' => 2, 'userId' => 1));
        $expected[] = $this->mockDataObject(array('objectType' => 'course', 'objectId' => 1, 'userId' => 2));
        $expected[] = $this->mockDataObject(array('objectType' => 'course', 'objectId' => 2, 'userId' => 2));

        return $expected;
    }

    protected function getDefaultMockFields()
    {
        return array(
            'objectType' => 'course',
            'objectId' => 1,
            'userId' => 1,
            'content' => 'comment content',
            'createdTime' => time(),
        );
    }
}
