<?php

namespace Tests\Unit\ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Resource\Course\CourseMember;
use ApiBundle\ApiTestCase;
use Symfony\Component\HttpFoundation\Request;

class CourseMemberTest extends ApiTestCase
{
    /**
     * @expectedException ApiBundle\Api\Exception\ResourceNotFoundException
     */
    public function testAddWithError()
    {
        $res = new CourseMember($this->getBiz());
        $res->add(Request::create(''), 100000);
    }

    /**
     * @expectedException ApiBundle\Api\Exception\InvalidArgumentException
     */
    public function testAddWithCourseNorFree()
    {

        $fakeCourse = array(
            'id' => 1,
            'title' => 'hello bike',
            'price' => 100,
            'createdTime' => time()
        );
        $this->mockBiz('Course:CourseService', array(
            array('functionName' => 'getCourse', 'runTimes' => 1, 'returnValue' => $fakeCourse)
        ));
        $res = new CourseMember($this->getBiz());
        $res->add(Request::create(''), 100000);
    }

    public function testAddWithSuccess()
    {
        $fakeCourse = array(
            'id' => 1,
            'title' => 'hello bike',
            'price' => 0,
            'createdTime' => time()
        );
        $this->mockBiz('Course:CourseService', array(
            array('functionName' => 'getCourse', 'runTimes' => 1, 'returnValue' => $fakeCourse)
        ));

        $this->mockBiz('Course:MemberService', array(
            array('functionName' => 'becomeStudent', 'runTimes' => 1, 'returnValue' => 1)
        ));
        $res = new CourseMember($this->getBiz());
        $resp = $res->add(Request::create(''), 100000);

        $this->assertEquals(array('success' => true), $resp);
    }
}