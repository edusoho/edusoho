<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use AppBundle\Extensions\DataTag\ReviewPostsDataTag;
use Biz\BaseTestCase;

class ReviewPostsDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetDataArgumentError()
    {
        $datatag = new ReviewPostsDataTag();
        $datatag->getData([]);
    }

    public function testGetClassroomData()
    {
        $reviews = [
            ['id' => 1],
            ['id' => 2],
        ];
        $this->mockBiz('Classroom:ClassroomReviewService', [
            [
                'functionName' => 'searchReviews',
                'returnValue' => $reviews,
            ],
        ]);

        $datatag = new ReviewPostsDataTag();
        $result = $datatag->getData(['reviewId' => 1, 'targetType' => 'classroom']);
        $this->assertArrayEquals($reviews, $result);
    }

    public function testGetCourseData()
    {
        $reviews = [
            ['id' => 1],
            ['id' => 2],
        ];
        $this->mockBiz('Review:ReviewService', [
            [
                'functionName' => 'searchReview',
                'returnValue' => $reviews,
            ],
        ]);

        $datatag = new ReviewPostsDataTag();
        $result = $datatag->getData(['reviewId' => 1, 'targetType' => 'course']);
        $this->assertArrayEquals($reviews, $result);
    }
}
