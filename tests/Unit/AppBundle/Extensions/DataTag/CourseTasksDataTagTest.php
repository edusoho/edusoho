<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use Biz\Course\Service\CourseService;
use Biz\Task\Service\TaskService;
use AppBundle\Extensions\DataTag\CourseLessonsDataTag;

class CourseTasksDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $course = array(
            'title' => 'online test course 1',
            'courseSetId' => 1,
            'expiryMode' => 'days',
            'expiryDays' => 1,
            'learnMode' => 'freeMode',
        );
        $course = $this->getCourseService()->createCourse($course);

        $task = array(
            'fromCourseId' => $course['id'],
            'fromCourseSetId' => $course['courseSetId'],
            'title' => 'test lesson 1',
            'content' => 'test lesson content 1',
            'type' => 'text',
            'mediaType' => 'video',
            'ext' => array('mediaSource' => 'youku', 'mediaUri' => 1),
        );
        $lesson = $this->getTaskService()->createTask($task);

        $datatag = new CourseLessonsDataTag();
        $lessons = $datatag->getData(array('courseId' => $course['id']));

        $this->assertEquals(1, count($lessons));

        $foundLesson = array_pop($lessons);
        $this->assertEquals($lesson['id'], $foundLesson['id']);
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course:CourseService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->getServiceKernel()->createService('Task:TaskService');
    }
}
