<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\LatestCourseThreadsDataTag;

class LatestCourseThreadsDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $course1 = array(
            'type' => 'normal',
            'title' => 'course1',
        );
        $course2 = array(
            'type' => 'normal',
            'title' => 'course2',
        );

        $course1 = $this->getCourseService()->createCourse($course1);

        $this->getCourseService()->publishCourse($course1['id']);

        $course2 = $this->getCourseService()->createCourse($course2);

        $this->getCourseService()->publishCourse($course2['id']);

        $thread1 = $this->getThreadService()->createThread(array(
            'courseId' => $course1['id'],
            'type' => 'question',
            'title' => 'question1',
            'content' => 'content1',
        ));
        $thread2 = $this->getThreadService()->createThread(array(
            'courseId' => $course1['id'],
            'type' => 'discussion',
            'title' => 'question2',
            'content' => 'content2',
        ));
        $thread3 = $this->getThreadService()->createThread(array(
            'courseId' => $course1['id'],
            'type' => 'question',
            'title' => 'question3',
            'content' => 'content3',
        ));
        $thread4 = $this->getThreadService()->createThread(array(
            'courseId' => $course2['id'],
            'type' => 'question',
            'title' => 'question4',
            'content' => 'content4',
        ));

        $datatag = new LatestCourseThreadsDataTag();
        $threads1 = $datatag->getData(array('count' => 5, 'courseId' => $course1['id']));
        $this->assertEquals(3, count($threads1) - 1);
        $threads2 = $datatag->getData(array('count' => 5, 'courseId' => $course2['id']));
        $this->assertEquals(1, count($threads2) - 1);
    }

    public function getThreadService()
    {
        return $this->getServiceKernel()->createService('Course:ThreadService');
    }

    public function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course:CourseService');
    }
}
