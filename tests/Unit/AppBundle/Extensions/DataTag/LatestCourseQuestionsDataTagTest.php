<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\LatestCourseQuestionsDataTag;

class LatestCourseQuestionsDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCountMissing()
    {
        $datatag = new LatestCourseQuestionsDataTag();
        $datatag->getData(array());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCountError()
    {
        $datatag = new LatestCourseQuestionsDataTag();
        $datatag->getData(array('count' => 101));
    }

    public function testGetData()
    {
        $courseSet = $this->getCourseSetService()->createCourseSet(array('type' => 'normal', 'title' => 'course set1 title'));

        $course1 = $this->getCourseService()->createCourse(array('title' => 'course title', 'courseSetId' => $courseSet['id'], 'expiryMode' => 'forever', 'learnMode' => 'freeMode', 'courseType' => 'default'));
        $this->getCourseService()->publishCourse($course1['id']);
        $course2 = $this->getCourseService()->createCourse(array('title' => 'course2 title', 'courseSetId' => $courseSet['id'], 'expiryMode' => 'forever', 'learnMode' => 'freeMode', 'courseType' => 'normal'));
        $this->getCourseService()->publishCourse($course2['id']);

        $question1 = $this->getThreadService()->createThread(array(
            'courseId' => $course1['id'],
            'type' => 'question',
            'title' => 'question1',
            'content' => 'content1',
        ));
        $question2 = $this->getThreadService()->createThread(array(
            'courseId' => $course1['id'],
            'type' => 'discussion',
            'title' => 'question2',
            'content' => 'content2',
        ));
        $question3 = $this->getThreadService()->createThread(array(
            'courseId' => $course1['id'],
            'type' => 'question',
            'title' => 'question3',
            'content' => 'content3',
        ));
        $question4 = $this->getThreadService()->createThread(array(
            'courseId' => $course2['id'],
            'type' => 'question',
            'title' => 'question4',
            'content' => 'content4',
        ));

        $datatag = new LatestCourseQuestionsDataTag();
        $questions = $datatag->getData(array('courseId' => $course1['id'], 'count' => 5));
        $this->assertEquals(2, count($questions));

        $questions = $datatag->getData(array('count' => 5));
        $this->assertEquals(3, count($questions));
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
