<?php

namespace Tests\Unit\Course\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class ReviewDaoTest extends BaseDaoTestCase
{
    public function testGetReviewByUserIdAndCourseId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject();

        $res = array();
        $res[] = $this->getDao()->getReviewByUserIdAndCourseId(1, 1);
        $res[] = $this->getDao()->getReviewByUserIdAndCourseId(1, 2);
        $res[] = $this->getDao()->getReviewByUserIdAndCourseId(2, 1);

        $this->assertEquals($expected[0], $res[0]);
        $this->assertNull($res[1]);
        $this->assertNull($res[2]);
    }

    public function testSumRatingByParams()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('private' => 0));
        $expected[] = $this->mockDataObject(array('rating' => 5000));
        $expected[] = $this->mockDataObject(array('rating' => 9999));

        $res = array();
        $res[] = $this->getDao()->sumRatingByParams(array('private' => 0));
        $res[] = $this->getDao()->sumRatingByParams(array('rating' => 5000));
        $res[] = $this->getDao()->sumRatingByParams(array());

        $this->assertEquals(1, $res[0]);
        $this->assertEquals(5000, $res[1]);
        $this->assertEquals(15000, $res[2]);
    }

    public function testSearch()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('private' => 0, 'userId' => 2));
        $expected[] = $this->mockDataObject(array('rating' => 5000, 'content' => '?'));
        $expected[] = $this->mockDataObject(array('rating' => 9999, 'courseId' => 2));

        $testConditions = array(
            array(
                'condition' => array('private' => 0),
                'expectedResults' => array($expected[0]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array(),
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ),
            array(
                'condition' => array('rating' => 5000),
                'expectedResults' => array($expected[1]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('userId' => 1),
                'expectedResults' => array($expected[1], $expected[2]),
                'expectedCount' => 2,
            ),
            array(
                'condition' => array('courseId' => 1),
                'expectedResults' => array($expected[0], $expected[1]),
                'expectedCount' => 2,
            ),
            array(
                'condition' => array('courseIds' => array(1, 2)),
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ),
            array(
                'condition' => array('courseSetId' => 1),
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ),
            array(
                'condition' => array('parentId' => 0),
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ),
        );

        $this->searchTestUtil($this->getDao(), $testConditions, $this->getCompareKeys());
    }

    protected function getDefaultMockFields()
    {
        return array(
            'userId' => 1,
            'courseId' => 1,
            'title' => 'hmm',
            'content' => 'ughh',
            'rating' => 1,
            'private' => 1,
            'parentId' => 0,
            'meta' => 'great',
            'courseSetId' => 1,
        );
    }
}
