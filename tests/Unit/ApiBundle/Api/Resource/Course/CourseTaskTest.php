<?php

namespace Tests\Unit\ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Resource\Course\CourseTask;
use ApiBundle\ApiTestCase;
use Symfony\Component\HttpFoundation\Request;

class CourseTaskTest extends ApiTestCase
{
    /**
     * @expectedException ApiBundle\Api\Exception\ResourceNotFoundException
     */
    public function testGetWithError()
    {
        $res = new CourseTask($this->getBiz());
        $res->search(Request::create(''), 100000);
    }

    public function testWithSuccess()
    {

        $tasks = array(
            array('id' => 1, 'title' => '123'),
            array('id' => 2, 'title' => '456'),
            array('id' => 3, 'title' => '789'),
        );
        $this->mockBiz('Course:CourseService',array(
            array('functionName' => 'getCourse', 'runTimes' => 1, 'returnValue' => 1)
        ));

        $this->mockBiz('Task:TaskService', array(
            array('functionName' => 'findTasksByCourseId', 'runTimes' => 1, 'returnValue' => $tasks)
        ));
        $res = new CourseTask($this->getBiz());
        $resp = $res->search(Request::create(''), 100000);

        $this->assertEquals($tasks, $resp);
    }
}