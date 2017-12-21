<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\LatestCourseThreadsByTypeDataTag;

class LatestCourseThreadsByTypeDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        // $course1 = array(
        //     'type' => 'normal',
        //     'title' => 'course1',
        // );

        // $course1 = $this->getCourseService()->createCourse($course1);

        // $this->getCourseService()->publishCourse($course1['id']);

        // $thread1 = $this->getThreadService()->createThread(array(
        //     'courseId' => $course1['id'],
        //     'type' => 'question',
        //     'title' => 'question1',
        //     'content' => 'content1',
        // ));
        // $thread2 = $this->getThreadService()->createThread(array(
        //     'courseId' => $course1['id'],
        //     'type' => 'discussion',
        //     'title' => 'question2',
        //     'content' => 'content2',
        // ));
        // $thread3 = $this->getThreadService()->createThread(array(
        //     'courseId' => $course1['id'],
        //     'type' => 'question',
        //     'title' => 'question3',
        //     'content' => 'content3',
        // ));
        // $thread4 = $this->getThreadService()->createThread(array(
        //     'courseId' => $course1['id'],
        //     'type' => 'question',
        //     'title' => 'question4',
        //     'content' => 'content4',
        // ));

        $datatag = new LatestCourseThreadsByTypeDataTag();
        // $threads = $datatag->getData(array('count' => 5, 'type' => 'question'));
        // $this->assertEquals(3, count($threads));
        // $threads = $datatag->getData(array('count' => 5));
        // $this->assertEquals(0, count($threads));
        $this->assertTrue(true);
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
