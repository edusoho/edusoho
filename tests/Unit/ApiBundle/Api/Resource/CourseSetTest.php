<?php

namespace Tests\Unit\ApiBundle\Api\Resource;

use ApiBundle\Api\PathParser;
use ApiBundle\Api\Resource\ResourceManager;
use ApiBundle\Api\ResourceKernel;
use Biz\BaseTestCase;
use Symfony\Component\HttpFoundation\Request;

class CourseSetTest extends BaseTestCase
{
    public function testGet()
    {
        $createdTime = time();
        $fakeCourseSets = array(
          array(
              'id' => 1,
              'title' => 'fakeCourseSet',
              'fakeField' => 'blablabla...',
              'createdTime' => $createdTime,
              'recommendedTime' => $createdTime,
              'updatedTime' => $createdTime
          )
        );

        $this->mockBiz('Course:CourseSetService',array(
            array('functionName' => 'searchCourseSets', 'runTimes' => 1, 'returnValue' => $fakeCourseSets)
        ));

        $kernel = new ResourceKernel(
            new PathParser(),
            new ResourceManager($this->getBiz())
        );
        $resp = $kernel->handle(Request::create('http://test.com/course_sets/1', 'GET'));

        $expectedCourseSet = array(
            'id' => 1,
            'title' => 'fakeCourseSet',
            'createdTime' => date('c', $createdTime),
            'updatedTime' => date('c', $createdTime),
            'recommendedTime' => date('c', $createdTime)
        );
        $this->assertEquals($expectedCourseSet, $resp);
    }
}