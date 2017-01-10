<?php

namespace Tests\Course\Dao;

use Tests\Base\BaseDaoTestCase;

class ReviewDaoTest extends BaseDaoTestCase
{
    public function testGetReviewByUserIdAndCourseId()
    {
        $factor = array();
        $factor[] = $this->mockDataObject();
        $factor[] = $this->mockDataObject();

        $res = array();
        $res[] = $this->getDao()->getReviewByUserIdAndCourseId(1, 1);
        $res[] = $this->getDao()->getReviewByUserIdAndCourseId(1, 2);
        $res[] = $this->getDao()->getReviewByUserIdAndCourseId(2, 1);

        $this->assertEquals($factor[0], $res[0]);
        $this->assertNull($res[1]);
        $this->assertNull($res[2]);
    }

    public function testSumRatingByParams()
    {
        $factor = array();
        $factor[] = $this->mockDataObject(array('private' => 0));
        $factor[] = $this->mockDataObject(array('rating' => 5000));
        $factor[] = $this->mockDataObject(array('rating' => 9999));

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
        $factor = array();
        $factor[] = $this->mockDataObject(array('private' => 0, 'userId' => 2));
        $factor[] = $this->mockDataObject(array('rating' => 5000, 'content' => '?'));
        $factor[] = $this->mockDataObject(array('rating' => 9999, 'courseId' => 2));

        $testConditions = array(
            array(
                'condition' => array('private' => 0),
                'expectedResults' => array($factor[0]),
                'expectedCount' => 1
            ),
            array(
                'condition' => array(),
                'expectedResults' => $factor,
                'expectedCount' => 3
            ),
            array(
                'condition' => array('rating' => 5000),
                'expectedResults' => array($factor[1]),
                'expectedCount' => 1
            ),
            array(
                'condition' => array('userId' => 1),
                'expectedResults' => array($factor[1], $factor[2]),
                'expectedCount' => 2
            ),
            array(
                'condition' => array('courseId' => 1),
                'expectedResults' => array($factor[0], $factor[1]),
                'expectedCount' => 2
            ),
            array(
                'condition' => array('courseIds' => array(1, 2)),
                'expectedResults' => $factor,
                'expectedCount' => 3
            ),
            array(
                'condition' => array('courseSetId' => 1),
                'expectedResults' => $factor,
                'expectedCount' => 3
            ),
            array(
                'condition' => array('parentId' => 0),
                'expectedResults' => $factor,
                'expectedCount' => 3
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
            'courseSetId' => 1
        );
    }
}
