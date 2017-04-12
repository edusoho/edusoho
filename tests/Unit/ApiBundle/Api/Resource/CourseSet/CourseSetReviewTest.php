<?php

namespace Tests\Unit\ApiBundle\Api\Resource\CourseSet;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\CourseSet\CourseSetReview;
use ApiBundle\ApiTestCase;

class CourseSetReviewTest extends ApiTestCase
{
    /**
     * @expectedException \ApiBundle\Api\Exception\ResourceNotFoundException
     */
    public function testSearchWithError()
    {
        $res = new CourseSetReview($this->getBiz());
        $res->search(new ApiRequest('', ''), 100000);
    }

    public function testSearchWithSuccess()
    {
        $expected = array(
            array('id' => 1, 'title' => '123'),
            array('id' => 2, 'title' => '456'),
            array('id' => 3, 'title' => '789'),
        );
        $this->mockBiz('Course:CourseSetService', array(
            array('functionName' => 'getCourseSet', 'runTimes' => 1, 'returnValue' => 1),
        ));

        $this->mockBiz('Course:ReviewService', array(
            array('functionName' => 'searchReviews', 'runTimes' => 1, 'returnValue' => $expected),
            array('functionName' => 'searchReviewsCount', 'runTimes' => 1, 'returnValue' => 100),
        ));
        $paging = array(
            'offset' => 10,
            'limit' => 20,
            'total' => 100,
        );
        $res = new CourseSetReview($this->getBiz());
        $resp = $res->search(new ApiRequest('', '', array('offset' => 10, 'limit' => 20)), 1);

        $this->assertEquals($expected, $resp['data']);
        $this->assertEquals($paging, $resp['paging']);
    }
}
