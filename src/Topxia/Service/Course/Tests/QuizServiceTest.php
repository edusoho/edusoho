<?php
namespace Topxia\Service\Course\Tests;

use Topxia\Service\Common\ServiceException;
use Topxia\Service\Common\BaseTestCase;
use Topxia\Common\ArrayToolkit;

class QuizServiceTest extends BaseTestCase
{
    
    public function testcreateItem()
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
            'type' => 'text'
        );
        $createdLesson = $this->getCourseService()->createLesson($lessonInfo);

       $createdLessonQuizItem = $this->getQuizService()->createItem(
            array(
                'description'=>'description',
                'level'=>'normal',
                'choices'=>array('1','2','3','4'),
                'answers'=>array(1,2),
                'courseId'=>$course['id'],
                'lessonId'=>$createdLesson['id'])
            );

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
            'type' => 'text'
        );
        $createdLesson = $this->getCourseService()->createLesson($lessonInfo);

        $createdLessonQuizItem = $this->getQuizService()->createItem(
            array(
                'description'=>'description',
                'level'=>'normal',
                'choices'=>array('1','2','3','4'),
                'answers'=>array(1,2),
                'courseId'=>$course['id'],
                'lessonId'=>$createdLesson['id'])
            );

       $getQuizItem = $this->getQuizService()->getQuizItem($createdLessonQuizItem['id']);

       $this->assertEquals($createdLessonQuizItem, $getQuizItem);
    }

    public function testupdateItem()
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
            'type' => 'text'
        );
        $createdLesson = $this->getCourseService()->createLesson($lessonInfo);

        $createdLessonQuizItem = $this->getQuizService()->createItem(
            array(
                'description'=>'description',
                'level'=>'normal',
                'choices'=>array('1','2','3','4'),
                'answers'=>array(1,2),
                'courseId'=>$course['id'],
                'lessonId'=>$createdLesson['id'])
            );

        $updateInfo = array(
            'description'=>'lessonQuizItemInfo_update_description',
            'level'=>'normal',
            'choices'=>array('1','2','3','4'),
            'answers'=>array(1,2),
            'level'=>'high');

        $updatedLessonQuizItem = $this->getQuizService()->updateItem($createdLessonQuizItem['id'], $updateInfo);

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
            'type' => 'text'
        );
        $createdLesson = $this->getCourseService()->createLesson($lessonInfo);

       $createdLessonQuizItem = $this->getQuizService()->createItem(
            array(
                'description'=>'description',
                'level'=>'normal',
                'choices'=>array('1','2','3','4'),
                'answers'=>array(1,2),
                'courseId'=>$course['id'],
                'lessonId'=>$createdLesson['id'])
            );
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
            'type' => 'text'
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
            'type' => 'text'
        );
        $createdLesson = $this->getCourseService()->createLesson($lessonInfo);

        $createdLessonQuizItem = $this->getQuizService()->createItem(
            array(
                'description'=>'description',
                'level'=>'normal',
                'choices'=>array('1','2','3','4'),
                'answers'=>array(1,2),
                'courseId'=>$course['id'],
                'lessonId'=>$createdLesson['id'])
            );

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
            'type' => 'text'
        );
        $createdLesson = $this->getCourseService()->createLesson($lessonInfo);

       $createdLessonQuizItem = $this->getQuizService()->createItem(
            array(
                'description'=>'description',
                'level'=>'normal',
                'choices'=>array('1','2','3','4'),
                'answers'=>array(1,2),
                'courseId'=>$course['id'],
                'lessonId'=>$createdLesson['id'])
            );

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
            'type' => 'text'
        );
        $createdLesson = $this->getCourseService()->createLesson($lessonInfo);

       $createdLessonQuizItem = $this->getQuizService()->createItem(
            array(
                'description'=>'description',
                'level'=>'normal',
                'choices'=>array('1','2','3','4'),
                'answers'=>array(1,2),
                'courseId'=>$course['id'],
                'lessonId'=>$createdLesson['id'])
            );
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
            'type' => 'text'
        );
        $createdLesson = $this->getCourseService()->createLesson($lessonInfo);

       $item1 = $this->getQuizService()->createItem(
            array(
                'description'=>'description',
                'level'=>'normal',
                'choices'=>array('1','2','3','4'),
                'answers'=>array(1,2),
                'courseId'=>$course['id'],
                'lessonId'=>$createdLesson['id'])
            );
         $item2 = $this->getQuizService()->createItem(
            array(
                'description'=>'description',
                'level'=>'normal',
                'choices'=>array('1','2','3','4'),
                'answers'=>array(1,2),
                'courseId'=>$course['id'],
                'lessonId'=>$createdLesson['id'])
            );
       
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
            'type' => 'text'
        );
        $createdLesson = $this->getCourseService()->createLesson($lessonInfo);

       $item1 = $this->getQuizService()->createItem(
            array(
                'description'=>'description',
                'level'=>'normal',
                'choices'=>array('1','2','3','4'),
                'answers'=>array(1,2),
                'courseId'=>$course['id'],
                'lessonId'=>$createdLesson['id'])
            );
       $item2 = $this->getQuizService()->createItem(
            array(
                'description'=>'description',
                'level'=>'normal',
                'choices'=>array('1','2','3','4'),
                'answers'=>array(1,2),
                'courseId'=>$course['id'],
                'lessonId'=>$createdLesson['id'])
            );
        $createdLessonQuiz = $this->getQuizService()->createLessonQuiz($course['id'], $createdLesson['id'], array($item1['id'], $item2['id']));
        $quizItems = $this->getQuizService()->findQuizItemsInLessonQuiz($createdLessonQuiz['id']);
        $this->assertEquals(2, count($quizItems));
    }

    public function testanswerQuizItem()
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
            'type' => 'text'
        );
        $createdLesson = $this->getCourseService()->createLesson($lessonInfo);

        $item1 = $this->getQuizService()->createItem(
            array(
                'description'=>'description',
                'level'=>'normal',
                'choices'=>array('1','2','3','4'),
                'answers'=>array(1,2),
                'courseId'=>$course['id'],
                'lessonId'=>$createdLesson['id'])
            );
        $createdLessonQuiz = $this->getQuizService()->createLessonQuiz($course['id'], $createdLesson['id'], array($item1['id']));
        $result = $this->getQuizService()->answerQuizItem($createdLessonQuiz['id'], $item1['id'], array(1,2));
        $this->assertEquals(1, $result['correct']);
    }

    public function testsubmitQuizResult()
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
            'type' => 'text'
        );
        $createdLesson = $this->getCourseService()->createLesson($lessonInfo);
        $item1 = $this->getQuizService()->createItem(
            array(
                'description'=>'description',
                'level'=>'normal',
                'choices'=>array('1','2','3','4'),
                'answers'=>array(1,2),
                'courseId'=>$course['id'],
                'lessonId'=>$createdLesson['id'])
            );
        $createdLessonQuiz = $this->getQuizService()->createLessonQuiz($course['id'], $createdLesson['id'], array($item1['id']));
        $result = $this->getQuizService()->submitQuizResult($createdLessonQuiz['id']); 
        
        $this->assertEquals(0, $result['score']);
        $this->assertEquals(0, $result['correctCount']);

        $this->getQuizService()->answerQuizItem($createdLessonQuiz['id'], $item1['id'], array(1,2));
        $result = $this->getQuizService()->submitQuizResult($createdLessonQuiz['id']);        
        $this->assertEquals(100, $result['score']);
        $this->assertEquals(1, $result['correctCount']);       
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