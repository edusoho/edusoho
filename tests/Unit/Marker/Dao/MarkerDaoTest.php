<?php

namespace Tests\Unit\Marker\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class MarkerDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('mediaId' => 3));
        $expected[] = $this->mockDataObject(array('second' => 3));

        $testConditions = array(
            array(
                'condition' => array(),
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ),
            array(
                'condition' => array('mediaId' => 3),
                'expectedResults' => array($expected[1]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('second' => 3),
                'expectedResults' => array($expected[2]),
                'expectedCount' => 1,
            ),
        );

        $this->searchTestUtil($this->getDao(), $testConditions, $this->getCompareKeys());
    }

    public function testFindByMediaId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('mediaId' => 3));
        $result = $this->getDao()->findByMediaId(3);
        $this->assertArrayEquals($expected[1], $result[0], $this->getCompareKeys());
    }

    public function testFindByIds()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('second' => 3));
        $result = $this->getDao()->findByIds(array(1, 2));
        $this->assertArrayEquals($expected[1], $result[1], $this->getCompareKeys());
    }

    protected function getDefaultMockFields()
    {
        return array(
            'second' => 2,
            'mediaId' => 2,
        );
    }
}