<?php

namespace Tests\Course\Dao;

use Tests\Base\BaseDaoTestCase;

class CourseSetDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $factor = array();
        $factor[] = $this->mockDataObject();
        $factor[] = $this->mockDataObject();
        $factor[] = $this->mockDataObject();

        $testConditions = array(
            array(
                'condition' => array('id' => range(1, 3)),
                'expectedResults' => $factor,
                'expectedCount' => 3
            ),
            array(
                'condition' => array('status' => 'draft'),
                'expectedResults' => $factor,
                'expectedCount' => 3
            )
        );

        $this->searchTestUtil($this->getDao(), $testConditions, $this->getCompareKeys());
    }

    public function testFindByIds()
    {
        $factor = array();
        for ($i = 0; $i < 10; $i++) {
            $factor[] = $this->mockDataObject();
        }
        
        $res = $this->getDao()->findByIds(range(1, 10));

        $this->assertEquals($factor, $res);
    }

    protected function getDefaultMockFields()
    {
        return array(
            'type' => 'course',
            'title' => 'hmm',
            'subtitle' => 'oh',
            'status' => 'draft',
            'serializeMode' => 'none',
            'ratingNum' => 1,
            'rating' => 1,
            'noteNum' => 1,
            'studentNum' => 1
        );
    }
}
