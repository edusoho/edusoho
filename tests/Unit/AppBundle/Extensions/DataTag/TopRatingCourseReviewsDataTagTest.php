<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\TopRatingCourseReviewsDataTag;

class TopRatingCourseReviewsDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testEmptyCount()
    {
        $dataTag = new TopRatingCourseReviewsDataTag();
        $dataTag->getData(array());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testMaxCount()
    {
        $dataTag = new TopRatingCourseReviewsDataTag();
        $dataTag->getData(array('count' => 101));
    }

    public function testGetData()
    {
        $dataTag = new TopRatingCourseReviewsDataTag();

        $this->mockBiz('Course:ReviewService', array(
            array(
                'functionName' => 'searchReviews',
                'returnValue' => array(),
            ),
        ));

        $course = $dataTag->getData(array('categoryId' => 1, 'count' => 10));
        $this->assertEmpty($course);
    }
}
