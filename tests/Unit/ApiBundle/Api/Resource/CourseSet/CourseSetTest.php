<?php

namespace Tests\Unit\ApiBundle\Api\Resource\CourseSet;

use ApiBundle\Api\Resource\CourseSet\CourseSet;
use ApiBundle\ApiTestCase;
use Symfony\Component\HttpFoundation\Request;

class CourseSetTest extends ApiTestCase
{
    public function testGet()
    {
        $createdTime = time();
        $fakeCourseSet = array(
            'id' => 1,
            'title' => 'fakeCourseSet',
            'fakeField' => 'blablabla...',
            'creator' => $this->getCurrentUser()->id,
            'createdTime' => $createdTime,
            'recommendedTime' => $createdTime,
            'updatedTime' => $createdTime
        );

        $this->mockBiz('Course:CourseSetService',array(
            array('functionName' => 'getCourseSet', 'runTimes' => 1, 'returnValue' => $fakeCourseSet)
        ));

        $res = new CourseSet($this->getBiz());
        $resp = $res->get(Request::create(''), 1);

        $this->assertEquals($fakeCourseSet, $resp);
    }
}