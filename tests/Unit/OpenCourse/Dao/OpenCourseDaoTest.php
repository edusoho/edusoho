<?php

namespace Tests\Unit\OpenCourse\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class OpenCourseDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $expected = array();
        $expected[0] = $this->mockDataObject(array('updatedTime' => 2));
        $expected[1] = $this->mockDataObject(array('status' => 'closed'));
        $expected[2] = $this->mockDataObject(array('type' => 'b'));
        $expected[3] = $this->mockDataObject(array('title' => 'b'));
        $expected[4] = $this->mockDataObject(array('userId' => 2));
        $expected[5] = $this->mockDataObject(array('createdTime' => 2));
        $expected[6] = $this->mockDataObject(array('smallPicture' => 'b'));
        $expected[7] = $this->mockDataObject(array('parentId' => 3));
        $expected[8] = $this->mockDataObject(array('recommended' => 2));
        $expected[9] = $this->mockDataObject(array('locked' => '0'));
        $expected[10] = $this->mockDataObject(array('categoryId' => 2));
        $testCondition = array(
            array(
                'condition' => array(),
                'expectedResults' => $expected,
                'expectedCount' => 11,
                ),
//            array(
//                'condition' => array('updatedTime_GE' => $updatedTime),
//                'expectedResults' => array($expected[0]),
//                'expectedCount' => 1,
//                ),
            array(
                'condition' => array('status' => 'closed'),
                'expectedResults' => array($expected[1]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('type' => 'b'),
                'expectedResults' => array($expected[2]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('titleLike' => 'b'),
                'expectedResults' => array($expected[3]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('userId' => 2),
                'expectedResults' => array($expected[4]),
                'expectedCount' => 1,
                ),
//            array(
//                'condition' => array('startTime' => $createdTime, 'endTime' => $createdTime+1),
//                'expectedResults' => array($expected[5]),
//                'expectedCount' => 1,
//                ),
            array(
                'condition' => array('smallPicture' => 'b'),
                'expectedResults' => array($expected[6]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('parentId' => 3),
                'expectedResults' => array($expected[7]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('recommended' => 2),
                'expectedResults' => array($expected[8]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('locked' => 0),
                'expectedResults' => array($expected[9]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('categoryId' => 2),
                'expectedResults' => array($expected[10]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('categoryIds' => array(2)),
                'expectedResults' => array($expected[10]),
                'expectedCount' => 1,
                ),
            );
        $this->searchTestUtil($this->getDao(), $testCondition, $this->getCompareKeys());
    }

    public function testFindByIds()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject();
        $res = $this->getDao()->findByIds(array(1, 2, 3));
        $testFields = $this->getCompareKeys();
        foreach ($res as $key => $result) {
            $this->assertArrayEquals($expected[$key], $result, $testFields);
        }
    }

    protected function getDefaultMockFields()
    {
        return array(
            'updatedTime' => 1,
            'status' => 'draft',
            'type' => 'a',
            'title' => 'a',
            'userId' => 1,
            'createdTime' => 1,
            'categoryId' => 1,
            'smallPicture' => 'a',
            'parentId' => 2,
            'recommended' => 1,
            'locked' => 1,
            );
    }
}
