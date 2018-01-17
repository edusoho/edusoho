<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\LatestCourseThreadsByTypeDataTag;

class LatestCourseThreadsByTypeDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCountMissing()
    {
        $datatag = new LatestCourseThreadsByTypeDataTag();
        $datatag->getData(array());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCountError()
    {
        $datatag = new LatestCourseThreadsByTypeDataTag();
        $datatag->getData(array('count' => 101));
    }

    public function testGetData()
    {
        $courseSet = $this->getCourseSetService()->createCourseSet(array('type' => 'normal', 'title' => 'course set1 title'));

        $course1 = $this->getCourseService()->createCourse(array('title' => 'course title', 'courseSetId' => $courseSet['id'], 'expiryMode' => 'forever', 'learnMode' => 'freeMode', 'courseType' => 'default'));
        $this->getCourseService()->publishCourse($course1['id']);

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
            'courseId' => $course1['id'],
            'type' => 'question',
            'title' => 'question4',
            'content' => 'content4',
        ));

        $datatag = new LatestCourseThreadsByTypeDataTag();
        $threads = $datatag->getData(array('count' => 5, 'type' => 'question'));
        $this->assertEquals(3, count($threads));
        $this->assertEquals($course1['title'], $threads[0]['courseTitle']);

        $threads = $datatag->getData(array('count' => 5));
        $this->assertEquals(4, count($threads));
    }

    public function getThreadService()
    {
        return $this->createService('Course:ThreadService');
    }

    public function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    public function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }
}
