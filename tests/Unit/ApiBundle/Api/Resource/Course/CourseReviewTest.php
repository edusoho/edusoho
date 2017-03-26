<?php

namespace Tests\Unit\ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Resource\Course\CourseReview;
use ApiBundle\ApiTestCase;
use Symfony\Component\HttpFoundation\Request;

class CourseReviewTest extends ApiTestCase
{
    /**
     * @expectedException ApiBundle\Api\Exception\ResourceNotFoundException
     */
    public function testSearchWithError()
    {
        $res = new CourseReview($this->getBiz());
        $res->search(Request::create(''), 100000);
    }

    public function testSearchWithSuccess()
    {

        $expected = array(
            array('id' => 1, 'title' => '123'),
            array('id' => 2, 'title' => '456'),
            array('id' => 3, 'title' => '789'),
        );
        $this->mockBiz('Course:CourseService',array(
            array('functionName' => 'getCourse', 'runTimes' => 1, 'returnValue' => 1)
        ));

        $this->mockBiz('Course:ReviewService', array(
            array('functionName' => 'searchReviews', 'runTimes' => 1, 'returnValue' => $expected),
            array('functionName' => 'searchReviewsCount', 'runTimes' => 1, 'returnValue' => 100)
        ));
        $paging = array(
            'offset' => 10,
            'limit' => 20,
            'total' => 100
        );
        $res = new CourseReview($this->getBiz());
        $resp = $res->search(Request::create('', 'GET', $paging), 1);

        $this->assertEquals($expected, $resp['data']);
        $this->assertEquals($paging, $resp['paging']);
    }
}