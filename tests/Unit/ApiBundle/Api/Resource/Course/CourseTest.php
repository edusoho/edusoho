<?php

namespace Tests\Unit\ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Resource\Course\Course;
use ApiBundle\ApiTestCase;
use Symfony\Component\HttpFoundation\Request;

class CourseTest extends ApiTestCase
{
    /**
     * @expectedException ApiBundle\Api\Exception\ResourceNotFoundException
     */
    public function testGetWithError()
    {
        $courseRes = new Course($this->getBiz());
        $courseRes->get(Request::create(''), 100000);
    }

    public function testWithSuccess()
    {

        $fakeCourse = array(
            'id' => 1,
            'title' => 'hello bike',
            'createdTime' => time()
        );
        $this->mockBiz('Course:CourseService',array(
            array('functionName' => 'getCourse', 'runTimes' => 1, 'returnValue' => $fakeCourse)
        ));
        $courseRes = new Course($this->getBiz());
        $resp = $courseRes->get(Request::create(''), 100000);

        $this->assertNotNull($resp);
        $this->assertEquals($fakeCourse, $resp);
    }
}