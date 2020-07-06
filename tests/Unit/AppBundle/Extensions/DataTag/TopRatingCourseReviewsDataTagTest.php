<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use AppBundle\Extensions\DataTag\TopRatingCourseReviewsDataTag;
use Biz\BaseTestCase;

class TopRatingCourseReviewsDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testEmptyCount()
    {
        $dataTag = new TopRatingCourseReviewsDataTag();
        $dataTag->getData([]);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testMaxCount()
    {
        $dataTag = new TopRatingCourseReviewsDataTag();
        $dataTag->getData(['count' => 101]);
    }

    public function testGetData()
    {
        $dataTag = new TopRatingCourseReviewsDataTag();

        $this->mockBiz('Review:ReviewService', [
            [
                'functionName' => 'searchReviews',
                'returnValue' => [],
            ],
        ]);

        $course = $dataTag->getData(['categoryId' => 1, 'count' => 10]);
        $this->assertEmpty($course);
    }
}
