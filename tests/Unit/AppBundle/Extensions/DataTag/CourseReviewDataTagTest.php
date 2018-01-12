<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\CourseReviewDataTag;

class CourseReviewDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testArgumentError()
    {
        $datatag = new CourseReviewDataTag();
        $datatag->getData(array());
    }

    public function testGetData()
    {
        $datatag = new CourseReviewDataTag();

        $data = $datatag->getData(array('reviewId' => 1));
        $this->assertNull($data);

        $this->mockBiz('Course:ReviewService', array(
            array(
                'functionName' => 'getReview',
                'returnValue' => array('id' => 1, 'userId' => 2, 'courseId' => 3),
            ),
        ));

        $this->mockBiz('User:UserService', array(
            array(
                'functionName' => 'getUser',
                'returnValue' => array('id' => 2, 'nickname' => 'user name', 'password' => '123456', 'salt' => 'abcd'),
            ),
        ));

        $this->mockBiz('Course:CourseService', array(
            array(
                'functionName' => 'getCourse',
                'returnValue' => array('id' => 3, 'title' => 'course title'),
            ),
        ));

        $data = $datatag->getData(array('reviewId' => 1));

        $this->assertEquals(1, $data['id']);
        $this->assertArrayHasKey('reviewer', $data);
        $this->assertArrayHasKey('course', $data);
    }
}
