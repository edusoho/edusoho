<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use AppBundle\Extensions\DataTag\CourseReviewDataTag;
use Biz\BaseTestCase;

class CourseReviewDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testArgumentError()
    {
        $datatag = new CourseReviewDataTag();
        $datatag->getData([]);
    }

    public function testGetData()
    {
        $datatag = new CourseReviewDataTag();

        $data = $datatag->getData(['reviewId' => 1]);
        $this->assertNull($data);

        $this->mockBiz('Review:ReviewService', [
            [
                'functionName' => 'getReview',
                'returnValue' => ['id' => 1, 'userId' => 2, 'targetId' => 3],
            ],
        ]);

        $this->mockBiz('User:UserService', [
            [
                'functionName' => 'getUser',
                'returnValue' => ['id' => 2, 'nickname' => 'user name', 'password' => '123456', 'salt' => 'abcd'],
            ],
        ]);

        $this->mockBiz('Course:CourseService', [
            [
                'functionName' => 'getCourse',
                'returnValue' => ['id' => 3, 'title' => 'course title'],
            ],
        ]);

        $data = $datatag->getData(['reviewId' => 1]);

        $this->assertEquals(1, $data['id']);
        $this->assertArrayHasKey('reviewer', $data);
        $this->assertArrayHasKey('course', $data);
    }
}
