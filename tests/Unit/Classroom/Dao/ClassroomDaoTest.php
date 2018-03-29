<?php

namespace Tests\Unit\Classroom\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class ClassroomDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $expected = array();
        $expected[0] = $this->mockDataObject(array('status' => 'closed'));
        $expected[1] = $this->mockDataObject(array('title' => 'b'));
        $expected[2] = $this->mockDataObject(array('price' => 55.5));
        $expected[3] = $this->mockDataObject(array('private' => 1));
        $expected[4] = $this->mockDataObject(array('categoryId' => 2));
        $expected[5] = $this->mockDataObject(array('recommended' => 1));
        $expected[6] = $this->mockDataObject(array('showable' => 0));
        $expected[7] = $this->mockDataObject(array('buyable' => 0));
        $expected[8] = $this->mockDataObject(array('vipLevelId' => 1));
        $expected[9] = $this->mockDataObject(array('orgCode' => '0'));
        $expected[10] = $this->mockDataObject(array('headTeacherId' => 1));
        $testCondition = array(
            array(
                'condition' => array(),
                'expectedResults' => $expected,
                'expectedCount' => 11,
                ),
            array(
                'condition' => array('status' => 'closed'),
                'expectedResults' => array($expected[0]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('title' => 'b'),
                'expectedResults' => array($expected[1]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('titleLike' => 'b'),
                'expectedResults' => array($expected[1]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('price' => 55.5),
                'expectedResults' => array($expected[2]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('price_GT' => 50),
                'expectedResults' => array($expected[2]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('private' => 1),
                'expectedResults' => array($expected[3]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('categoryId' => 2),
                'expectedResults' => array($expected[4]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('recommended' => 1),
                'expectedResults' => array($expected[5]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('showable' => 0),
                'expectedResults' => array($expected[6]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('buyable' => 0),
                'expectedResults' => array($expected[7]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('vipLevelId' => 1),
                'expectedResults' => array($expected[8]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('orgCode' => '0'),
                'expectedResults' => array($expected[9]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('headTeacherId' => 1),
                'expectedResults' => array($expected[10]),
                'expectedCount' => 1,
                ),
            );
        $this->searchTestUtil($this->getDao(), $testCondition, $this->getCompareKeys());
    }

    public function testGetByTitle()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $res = $this->getDao()->getByTitle('a');
        $this->assertArrayEquals($expected[0], $res, $this->getCompareKeys());
    }

    public function testFindByLikeTitle()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('title' => 'ahaha'));
        $expected[] = $this->mockDataObject(array('title' => 'ayaya'));
        $res = $this->getDao()->findByLikeTitle('a');
        $this->assertArrayEquals($expected[0], $res[0], $this->getCompareKeys());
        $this->assertArrayEquals($expected[1], $res[1], $this->getCompareKeys());
    }

    public function testFindByIds()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('title' => 'ahaha'));
        $expected[] = $this->mockDataObject(array('title' => 'ahaha'));
        $res = $this->getDao()->findByIds(array(1, 2));
        $this->assertArrayEquals($expected[0], $res[0], $this->getCompareKeys());
        $this->assertArrayEquals($expected[1], $res[1], $this->getCompareKeys());
    }

    public function refreshHotSeq()
    {
        $expected = $this->mockDataObject(array('title' => 'ahaha', 'hotSeq' => 10));
        $this->assertEquals(10, $expected['hotSeq']);

        $this->getDao()->refreshHotSeq();

        $classroom = $this->getDao()->get($expected['id']);
        $this->assertEquals(0, $classroom['hotSeq']);
    }

    protected function getDefaultMockFields()
    {
        return array(
            'status' => 'draft',
            'title' => 'a',
            'price' => 1.1,
            'private' => 0,
            'categoryId' => 1,
            'recommended' => 0,
            'showable' => 1,
            'buyable' => 1,
            'vipLevelId' => 0,
            'orgCode' => '1',
            'headTeacherId' => 0,
            );
    }
}
