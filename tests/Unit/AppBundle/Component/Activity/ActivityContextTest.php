<?php

namespace Tests\Unit\Component\Activity;

use AppBundle\Component\Activity\ActivityContext;
use Biz\BaseTestCase;

class ActivityContextTest extends BaseTestCase
{
    public function testGetUser()
    {
        $context = $this->createActivityContext();
        $this->assertEquals($this->biz['user'], $context->getUser());
    }

    public function testGetCourseDraftEmpty()
    {
        $context = $this->createActivityContext();
        $draft = $context->getCourseDraft();
        $this->assertEmpty($draft);
    }

    public function testGetCourseDraft()
    {
        $context = $this->createActivityContext();
        $this->mockBiz('Course:CourseDraftService',
            array(
                array(
                    'functionName' => 'getCourseDraftByCourseIdAndActivityIdAndUserId',
                    'returnValue' => array(
                        'id' => 1,
                        'title' => 'test title',
                    ),
                ),
            )
        );
        $draft = $context->getCourseDraft();
        $this->assertEquals(array(
            'id' => 1,
            'title' => 'test title',
        ), $draft);
    }

    public function testGetCourse()
    {
        $context = $this->createActivityContext();
        $this->mockBiz('Course:CourseService',
            array(
                array(
                    'functionName' => 'getCourse',
                    'returnValue' => array(
                        'id' => 1,
                        'title' => 'test title',
                    ),
                ),
            )
        );
        $draft = $context->getCourse();
        $this->assertEquals(array(
            'id' => 1,
            'title' => 'test title',
        ), $draft);
    }

    public function testGetActivity()
    {
        $context = $this->createActivityContext();
        $draft = $context->getActivity();
        $this->assertEquals(array(
            'id' => 1,
            'fromCourseId' => 1,
            'type' => 'html_test',
            'title' => 'test title',
        ), $draft);
    }

    protected function createActivityContext()
    {
        $activity = array(
            'id' => 1,
            'fromCourseId' => 1,
            'type' => 'html_test',
            'title' => 'test title',
        );

        return new ActivityContext($this->biz, $activity);
    }
}
