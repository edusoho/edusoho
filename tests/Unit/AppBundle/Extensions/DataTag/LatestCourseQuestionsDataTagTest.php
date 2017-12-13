<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\LatestCourseQuestionsDataTag;

class LatestCourseQuestionsDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $course1 = array(
            'type' => 'normal',
            'title' => 'course1',
        );

        $course1 = $this->getCourseService()->createCourse($course1);

        $this->getCourseService()->publishCourse($course1['id']);

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
            'courseId' => $course1['id'],
            'type' => 'question',
            'title' => 'question4',
            'content' => 'content4',
        ));

        $datatag = new LatestCourseQuestionsDataTag();
        $questions = $datatag->getData(array('courseId' => $course1['id'], 'count' => 5));
        $this->assertEquals(3, count($questions));
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
