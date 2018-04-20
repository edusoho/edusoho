<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\ReviewPostsDataTag;

class ReviewPostsDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetDataArgumentError()
    {
        $datatag = new ReviewPostsDataTag();
        $datatag->getData(array());
    }

    public function testGetClassroomData()
    {
        $reviews = array(
            array('id' => 1),
            array('id' => 2),
        );
        $this->mockBiz('Classroom:ClassroomReviewService', array(
            array(
                'functionName' => 'searchReviews',
                'returnValue' => $reviews,
            ),
        ));

        $datatag = new ReviewPostsDataTag();
        $result = $datatag->getData(array('reviewId' => 1, 'targetType' => 'classroom'));
        $this->assertArrayEquals($reviews, $result);
    }

    public function testGetCourseData()
    {
        $reviews = array(
            array('id' => 1),
            array('id' => 2),
        );
        $this->mockBiz('Course:ReviewService', array(
            array(
                'functionName' => 'searchReviews',
                'returnValue' => $reviews,
            ),
        ));

        $datatag = new ReviewPostsDataTag();
        $result = $datatag->getData(array('reviewId' => 1, 'targetType' => 'course'));
        $this->assertArrayEquals($reviews, $result);
    }
}
