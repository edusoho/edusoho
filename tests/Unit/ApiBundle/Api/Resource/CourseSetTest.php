<?php

namespace Tests\Unit\ApiBundle\Api\Resource;

use ApiBundle\Api\PathParser;
use ApiBundle\Api\Resource\CourseSet\CourseSet;
use ApiBundle\Api\Resource\ResourceManager;
use ApiBundle\Api\ResourceKernel;
use ApiBundle\ApiTestCase;
use Biz\BaseTestCase;
use Symfony\Component\HttpFoundation\Request;

class CourseSetTest extends ApiTestCase
{
    public function testGet()
    {
        $createdTime = time();
        $fakeCourseSets = array(
          array(
              'id' => 1,
              'title' => 'fakeCourseSet',
              'fakeField' => 'blablabla...',
              'creator' => $this->getCurrentUser()->id,
              'createdTime' => $createdTime,
              'recommendedTime' => $createdTime,
              'updatedTime' => $createdTime
          )
        );

        $this->mockBiz('Course:CourseSetService',array(
            array('functionName' => 'searchCourseSets', 'runTimes' => 1, 'returnValue' => $fakeCourseSets)
        ));

        $res = new CourseSet($this->getBiz());
        $resp = $res->get(Request::create(''), 1);

        $this->assertEquals($fakeCourseSets[0], $resp);
    }
}