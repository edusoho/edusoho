<?php

namespace Tests\Unit\File\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class UploadFileShareHistoryDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('sourceUserId' => 3));
        $expected[] = $this->mockDataObject(array('targetUserId' => 2, 'isActive' => 1));

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
                'condition' => array('sourceUserId' => 2),
                'expectedResults' => array($expected[0], $expected[2]),
                'expectedCount' => 2,
            ),
            array(
                'condition' => array('targetUserId' => 3),
                'expectedResults' => array($expected[0], $expected[1]),
                'expectedCount' => 2,
            ),
            array(
                'condition' => array('isActive' => 1),
                'expectedResults' => array($expected[2]),
                'expectedCount' => 1,
            ),
        );

        $this->searchTestUtil($this->getDao(), $testConditions, $this->getCompareKeys());
    }

    public function testFindByUserId()
    {
        $expected = $this->mockDataObject();
        $result = $this->getDao()->findByUserId(2);
        $this->assertArrayEquals($expected, $result[0], $this->getCompareKeys());
    }

    protected function getDefaultMockFields()
    {
        return array(
            'sourceUserId' => 2,
            'targetUserId' => 3,
            'isActive' => 0,
            'createdTime' => 0,
        );
    }
}
