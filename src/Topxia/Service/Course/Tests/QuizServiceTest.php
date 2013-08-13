<?php
namespace Topxia\Service\Course\Tests;

use Topxia\Service\Common\ServiceException;
use Topxia\Service\Common\BaseTestCase;
use Topxia\Common\ArrayToolkit;

class QuizServiceTest extends BaseTestCase
{
    
    public function testCreateLessonQuizItem()
    {
        $courseInfo = array(
            'type' => 'online',
            'title' => 'online test course 1',
        );
        $course = $this->getCourseService()->createCourse($courseInfo);

        $lessonInfo = array(
            'courseId' => $course['id'],
            'title' => 'test lesson 1',
            'content' => 'test lesson content 1',
        );
        $createdLesson = $this->getCourseService()->createLesson($lessonInfo);

        $lessonQuizItemInfo = array(
            'description'=>'lessonQuizItemInfo_description',
            'choices'=>'lessonQuizItemInfo_choices',
            'answers'=>'lessonQuizItemInfo_answers',
            'level'=>'low'
            );

       $createdLessonQuizItem = $this->getQuizService()->createLessonQuizItem($course['id'],
            $createdLesson['id'], $lessonQuizItemInfo);

       $this->assertNotNull($createdLessonQuizItem);
    }

    public function testGetLessonQuizItem()
    {
        $courseInfo = array(
            'type' => 'online',
            'title' => 'online test course 1',
        );
        $course = $this->getCourseService()->createCourse($courseInfo);

        $lessonInfo = array(
            'courseId' => $course['id'],
            'title' => 'test lesson 1',
            'content' => 'test lesson content 1',
        );
        $createdLesson = $this->getCourseService()->createLesson($lessonInfo);

        $lessonQuizItemInfo = array(
            'description'=>'lessonQuizItemInfo_description',
            'choices'=>'lessonQuizItemInfo_choices',
            'answers'=>'lessonQuizItemInfo_answers',
            'level'=>'low'
            );

       $createdLessonQuizItem = $this->getQuizService()->createLessonQuizItem($course['id'],
            $createdLesson['id'], $lessonQuizItemInfo);

       $getQuizItem = $this->getQuizService()->getQuizItem($createdLessonQuizItem['id']);

       $this->assertEquals($createdLessonQuizItem, $getQuizItem);
    }

    public function testEditLessonQuizItem()
    {
        $courseInfo = array(
            'type' => 'online',
            'title' => 'online test course 1',
        );
        $course = $this->getCourseService()->createCourse($courseInfo);

        $lessonInfo = array(
            'courseId' => $course['id'],
            'title' => 'test lesson 1',
            'content' => 'test lesson content 1',
        );
        $createdLesson = $this->getCourseService()->createLesson($lessonInfo);

        $lessonQuizItemInfo = array(
            'description'=>'lessonQuizItemInfo_description',
            'choices'=>'lessonQuizItemInfo_choices',
            'answers'=>'lessonQuizItemInfo_answers',
            'level'=>'low'
            );

        $createdLessonQuizItem = $this->getQuizService()->createLessonQuizItem($course['id'],
            $createdLesson['id'], $lessonQuizItemInfo);

        $updateInfo = array(
            'description'=>'lessonQuizItemInfo_update_description',
            'choices'=>'lessonQuizItemInfo_update_choices',
            'answers'=>'lessonQuizItemInfo_update_answers',
            'level'=>'high');

        $updatedLessonQuizItem = $this->getQuizService()->editLessonQuizItem($createdLessonQuizItem['id'], $updateInfo);

        $this->assertEquals($updateInfo['description'], $updatedLessonQuizItem['description']);
        $this->assertEquals($updateInfo['answers'], $updatedLessonQuizItem['answers']);
        $this->assertEquals($updateInfo['level'], $updatedLessonQuizItem['level']);
    }

    public function testDeleteLessonQuizItem()
    {
        $courseInfo = array(
            'type' => 'online',
            'title' => 'online test course 1',
        );
        $course = $this->getCourseService()->createCourse($courseInfo);

        $lessonInfo = array(
            'courseId' => $course['id'],
            'title' => 'test lesson 1',
            'content' => 'test lesson content 1',
        );
        $createdLesson = $this->getCourseService()->createLesson($lessonInfo);

        $lessonQuizItemInfo = array(
            'description'=>'lessonQuizItemInfo_description',
            'choices'=>'lessonQuizItemInfo_choices',
            'answers'=>'lessonQuizItemInfo_answers',
            'level'=>'low'
            );

        $createdLessonQuizItem = $this->getQuizService()->createLessonQuizItem($course['id'],
            $createdLesson['id'], $lessonQuizItemInfo);

        $result = $this->getQuizService()->deleteQuizItem($createdLessonQuizItem['id']);
        $this->assertEquals(1, $result);
    }

    public function testGetUserLessonQuiz()
    {
        $courseInfo = array(
            'type' => 'online',
            'title' => 'online test course 1',
        );
        $course = $this->getCourseService()->createCourse($courseInfo);

        $lessonInfo = array(
            'courseId' => $course['id'],
            'title' => 'test lesson 1',
            'content' => 'test lesson content 1',
        );
        $createdLesson = $this->getCourseService()->createLesson($lessonInfo);

        $currentUser = $this->getCurrentUser();
        $getedLessonQuiz = $this->getQuizService()->getUserLessonQuiz($course['id'], $createdLesson['id'], $currentUser['id']);
        $this->assertEmpty($getedLessonQuiz);
    }
    public function testCreateLessonQuiz()
    {   
        $courseInfo = array(
            'type' => 'online',
            'title' => 'online test course 1',
        );
        $course = $this->getCourseService()->createCourse($courseInfo);

        $lessonInfo = array(
            'courseId' => $course['id'],
            'title' => 'test lesson 1',
            'content' => 'test lesson content 1',
        );
        $createdLesson = $this->getCourseService()->createLesson($lessonInfo);

        $lessonQuizItemInfo = array(
            'description'=>'lessonQuizItemInfo_description',
            'choices'=>'lessonQuizItemInfo_choices',
            'answers'=>'lessonQuizItemInfo_answers',
            'level'=>'low'
            );

        $createdLessonQuizItem = $this->getQuizService()->createLessonQuizItem($course['id'],
            $createdLesson['id'], $lessonQuizItemInfo);
        $createdLessonQuiz = $this->getQuizService()->createLessonQuiz($course['id'], $createdLesson['id'], array($createdLessonQuizItem['id']));
        $this->assertNotNull($createdLessonQuiz);
    }

    public function testDeleteLessonQuiz()
    {
        $courseInfo = array(
            'type' => 'online',
            'title' => 'online test course 1',
        );
        $course = $this->getCourseService()->createCourse($courseInfo);

        $lessonInfo = array(
            'courseId' => $course['id'],
            'title' => 'test lesson 1',
            'content' => 'test lesson content 1',
        );
        $createdLesson = $this->getCourseService()->createLesson($lessonInfo);

        $lessonQuizItemInfo = array(
            'description'=>'lessonQuizItemInfo_description',
            'choices'=>'lessonQuizItemInfo_choices',
            'answers'=>'lessonQuizItemInfo_answers',
            'level'=>'low'
            );

        $createdLessonQuizItem = $this->getQuizService()->createLessonQuizItem($course['id'],
            $createdLesson['id'], $lessonQuizItemInfo);
        $createdLessonQuiz = $this->getQuizService()->createLessonQuiz($course['id'], $createdLesson['id'], array($createdLessonQuizItem['id']));
        $getedLessonQuiz = $this->getQuizService()->getUserLessonQuiz($course['id'], $createdLesson['id'], $this->getCurrentUser()->id);
        $this->getQuizService()->deleteQuiz($createdLessonQuiz['id']);
        $this->assertNotNull($getedLessonQuiz);

        $getedLessonQuiz = $this->getQuizService()->getUserLessonQuiz($course['id'], $createdLesson['id'], $this->getCurrentUser()->id);
        $this->assertEmpty($getedLessonQuiz);
    }

    public function testFindLessonQuizItems()
    {
        $courseInfo = array(
            'type' => 'online',
            'title' => 'online test course 1',
        );
        $course = $this->getCourseService()->createCourse($courseInfo);

        $lessonInfo = array(
            'courseId' => $course['id'],
            'title' => 'test lesson 1',
            'content' => 'test lesson content 1',
        );
        $createdLesson = $this->getCourseService()->createLesson($lessonInfo);

        $lessonQuizItemInfo = array(
            'description'=>'lessonQuizItemInfo_description',
            'choices'=>'lessonQuizItemInfo_choices',
            'answers'=>'lessonQuizItemInfo_answers',
            'level'=>'low'
            );

        $createdLessonQuizItem = $this->getQuizService()->createLessonQuizItem($course['id'],
            $createdLesson['id'], $lessonQuizItemInfo);
        $createdLessonQuiz = $this->getQuizService()->createLessonQuiz($course['id'], $createdLesson['id'], array($createdLessonQuizItem['id']));
        $quizItems = $this->getQuizService()->findLessonQuizItems($course['id'], $createdLesson['id']);
        $this->assertEquals(1, count($quizItems));
    }

    public function testFindLessonQuizItemIds()
    {
        $courseInfo = array(
            'type' => 'online',
            'title' => 'online test course 1',
        );
        $course = $this->getCourseService()->createCourse($courseInfo);

        $lessonInfo = array(
            'courseId' => $course['id'],
            'title' => 'test lesson 1',
            'content' => 'test lesson content 1',
        );
        $createdLesson = $this->getCourseService()->createLesson($lessonInfo);

        $lessonQuizItemInfo = array(
            'description'=>'lessonQuizItemInfo_description',
            'choices'=>'lessonQuizItemInfo_choices',
            'answers'=>'lessonQuizItemInfo_answers',
            'level'=>'low'
            );

        $item1 = $this->getQuizService()->createLessonQuizItem($course['id'],
            $createdLesson['id'], $lessonQuizItemInfo);
        $item2 = $this->getQuizService()->createLessonQuizItem($course['id'],
            $createdLesson['id'], $lessonQuizItemInfo);
        $createdLessonQuiz = $this->getQuizService()->createLessonQuiz($course['id'], $createdLesson['id'], array($item1['id'], $item2['id']));
        $arrayIds = $this->getQuizService()->findLessonQuizItemIds($course['id'], $createdLesson['id']);
        $arrayIds = ArrayToolkit::column($arrayIds, 'id');
        $this->assertContains($item1['id'], $arrayIds);   
        $this->assertContains($item2['id'], $arrayIds);   
    }

    public function testFindQuizItemsInLessonQuiz()
    {
        $courseInfo = array(
            'type' => 'online',
            'title' => 'online test course 1',
        );
        $course = $this->getCourseService()->createCourse($courseInfo);

        $lessonInfo = array(
            'courseId' => $course['id'],
            'title' => 'test lesson 1',
            'content' => 'test lesson content 1',
        );
        $createdLesson = $this->getCourseService()->createLesson($lessonInfo);

        $lessonQuizItemInfo = array(
            'description'=>'lessonQuizItemInfo_description',
            'choices'=>'lessonQuizItemInfo_choices',
            'answers'=>'lessonQuizItemInfo_answers',
            'level'=>'low'
            );

        $item1 = $this->getQuizService()->createLessonQuizItem($course['id'],
            $createdLesson['id'], $lessonQuizItemInfo);
        $item2 = $this->getQuizService()->createLessonQuizItem($course['id'],
            $createdLesson['id'], $lessonQuizItemInfo);
        $createdLessonQuiz = $this->getQuizService()->createLessonQuiz($course['id'], $createdLesson['id'], array($item1['id'], $item2['id']));
        $quizItems = $this->getQuizService()->findQuizItemsInLessonQuiz($createdLessonQuiz['id']);
        $this->assertEquals(2, count($quizItems));
    }

    public function testAnswerLessonQuizItem()
    {
        $courseInfo = array(
            'type' => 'online',
            'title' => 'online test course 1',
        );
        $course = $this->getCourseService()->createCourse($courseInfo);

        $lessonInfo = array(
            'courseId' => $course['id'],
            'title' => 'test lesson 1',
            'content' => 'test lesson content 1',
        );
        $createdLesson = $this->getCourseService()->createLesson($lessonInfo);

        $lessonQuizItemInfo = array(
            'description'=>'lessonQuizItemInfo_description',
            'choices'=>'lessonQuizItemInfo_choices',
            'answers'=>'lessonQuizItemInfo_answers',
            'level'=>'low'
            );

        $item1 = $this->getQuizService()->createLessonQuizItem($course['id'],
            $createdLesson['id'], $lessonQuizItemInfo);
        $createdLessonQuiz = $this->getQuizService()->createLessonQuiz($course['id'], $createdLesson['id'], array($item1['id']));
        $result = $this->getQuizService()->answerLessonQuizItem($createdLessonQuiz['id'], $item1['id'], "lessonQuizItemInfo_answers");
        $this->assertEquals("correct", $result);
    }

    public function testCheckUserLessonQuizResult()
    {
        $courseInfo = array(
            'type' => 'online',
            'title' => 'online test course 1',
        );
        $course = $this->getCourseService()->createCourse($courseInfo);

        $lessonInfo = array(
            'courseId' => $course['id'],
            'title' => 'test lesson 1',
            'content' => 'test lesson content 1',
        );
        $createdLesson = $this->getCourseService()->createLesson($lessonInfo);

        $lessonQuizItemInfo = array(
            'description'=>'lessonQuizItemInfo_description',
            'choices'=>'lessonQuizItemInfo_choices',
            'answers'=>'lessonQuizItemInfo_answers',
            'level'=>'low'
            );

        $item1 = $this->getQuizService()->createLessonQuizItem($course['id'],
            $createdLesson['id'], $lessonQuizItemInfo);
        $createdLessonQuiz = $this->getQuizService()->createLessonQuiz($course['id'], $createdLesson['id'], array($item1['id']));
        $result = $this->getQuizService()->checkUserLessonQuizResult($createdLessonQuiz['id']); 
        
        $this->assertEquals(0, $result['score']);
        $this->assertEquals(0, $result['correctCount']);       
        $this->assertEquals(1, $result['wrongCount']);

        $this->getQuizService()->answerLessonQuizItem($createdLessonQuiz['id'], $item1['id'], "lessonQuizItemInfo_answers");
        $result = $this->getQuizService()->checkUserLessonQuizResult($createdLessonQuiz['id']);        
        $this->assertEquals(100, $result['score']);
        $this->assertEquals(1, $result['correctCount']);       
        $this->assertEquals(0, $result['wrongCount']);
    }

    private function getQuizService()
    {
        return $this->getServiceKernel()->createService('Course.QuizService');
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
}