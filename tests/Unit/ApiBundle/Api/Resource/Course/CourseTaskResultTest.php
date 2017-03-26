<?php

namespace Tests\Unit\ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Resource\Course\CourseTaskResult;
use ApiBundle\ApiTestCase;
use Symfony\Component\HttpFoundation\Request;

class CourseTaskResultTest extends ApiTestCase
{
    /**
     * @expectedException ApiBundle\Api\Exception\ResourceNotFoundException
     */
    public function testGetWithError()
    {
        $res = new CourseTaskResult($this->getBiz());
        $res->search(Request::create(''), 100000);
    }

    public function testWithSuccess()
    {

        $taskResults = array(
            array('id' => 1, 'title' => '123'),
            array('id' => 2, 'title' => '456'),
            array('id' => 3, 'title' => '789'),
        );
        $this->mockBiz('Course:CourseService',array(
            array('functionName' => 'getCourse', 'runTimes' => 1, 'returnValue' => 1)
        ));

        $this->mockBiz('Task:TaskResultService', array(
            array('functionName' => 'findUserTaskResultsByCourseId', 'runTimes' => 1, 'returnValue' => $taskResults)
        ));
        $res = new CourseTaskResult($this->getBiz());
        $resp = $res->search(Request::create(''), 100000);

        $this->assertEquals($taskResults, $resp);
    }
}