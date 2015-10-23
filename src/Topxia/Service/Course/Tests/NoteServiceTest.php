<?php
namespace Topxia\Service\Course\Tests;

use Topxia\Service\Course\NoteService;
use Topxia\Service\Common\ServiceException;
use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\Course\CourseService;
use Topxia\Common\ArrayToolkit;

class NoteServiceTest extends BaseTestCase
{

    public function testGetNote()
    {
        $currentUser = $this->getCurrentUser();

        $course = $this->getCourseService()->createCourse(array(
            'title' => 'test course'
        ));

        $lesson = $this->getCourseService()->createLesson(array(
            'courseId' => $course['id'],
            'title' => 'test lesson 1',
            'content' => 'test lesson content 1',
            'type' => 'text'
        ));

        $note = $this->getNoteService()->saveNote(array(
            'content'=>'note content',
            'lessonId'=>$lesson['id'],
            'courseId'=>$course['id']
        ));

        $foundNote = $this->getNoteService()->getNote($note['id']);
        $this->assertEquals($note['userId'], $foundNote['userId']);


    }


    public function testGetNoteWithNotExistNote()
    {
        $result = $this->getNoteService()->getNote(999);
        $this->assertEquals(false, $result);
    }

    public function testGetUserLessonNote()
    {   
        $user = $this->getCurrentUser();

        $course = $this->getCourseService()->createCourse(array(
            'title' => 'test course'
        ));

        $lesson = $this->getCourseService()->createLesson(array(
            'courseId' => $course['id'],
            'title' => 'test lesson 1',
            'content' => 'test lesson content 1',
            'type' => 'text'
        ));

        $note = $this->getNoteService()->saveNote(array(
            'content' => 'note_content1',
            'lessonId' => $lesson['id'],
            'courseId' => $course['id']
        ));

        $foundNote = $this->getNoteService()->getUserLessonNote($note['userId'], $note['lessonId']);

        $this->assertEquals($note['id'], $foundNote['id']);
        $this->assertEquals($note['userId'], $foundNote['userId']);
        $this->assertEquals($note['lessonId'], $foundNote['lessonId']);
    }

    /**
     * @todo
     */
    public function testFindUserCourseNotes()
    {

    }

    /**
     * @todo
     */
    public function testsearchNoteCount()
    {
        $user = $this->getCurrentUser();
        $courseInfo = array(
            'type' => 'online',
            'title' => 'online test course'
        );
        $createdCourse = $this->getCourseService()->createCourse($courseInfo);
        $lessonInfo1 = array(
            'courseId' => $createdCourse['id'],
            'title' => 'test lesson 1',
            'content' => 'test lesson content 1',
            'type' => 'text'
        );
        $createdLesson1 = $this->getCourseService()->createLesson($lessonInfo1);
        $lessonInfo2 = array(
            'courseId' => $createdCourse['id'],
            'title' => 'test lesson 1',
            'content' => 'test lesson content 1',
            'type' => 'text'
        );
        $createdLesson2 = $this->getCourseService()->createLesson($lessonInfo2);
        $createdNote1 = $this->getNoteService()->saveNote(array(
            'content' => 'note_content1',
            'lessonId' => $createdLesson1['id'],
            'courseId' => $createdCourse['id']
        ));

        $createdNote2 = $this->getNoteService()->saveNote(array(
            'content' => 'note_content1',
            'lessonId' => $createdLesson2['id'],
            'courseId' => $createdCourse['id']
        ));

        $resultCount = $this->getNoteService()->searchNoteCount(array('courseId'=>$createdCourse['id'], 'lessonId'=>$createdLesson2['id']));
        $this->assertEquals(1, $resultCount);
        $resultCount = $this->getNoteService()->searchNoteCount(array('courseId'=>$createdCourse['id']));
        $this->assertEquals(2, $resultCount);
    }

    /**
     * @todo
     */
    public function testSearchNotes()
    {

        $user = $this->getCurrentUser();
        $courseInfo = array(
            'type' => 'online',
            'title' => 'online test course'
        );
        $createdCourse = $this->getCourseService()->createCourse($courseInfo);
        $lessonInfo1 = array(
            'courseId' => $createdCourse['id'],
            'title' => 'test lesson 1',
            'content' => 'test lesson content 1',
            'type' => 'text'
        );
        $createdLesson1 = $this->getCourseService()->createLesson($lessonInfo1);
        $lessonInfo2 = array(
            'courseId' => $createdCourse['id'],
            'title' => 'test lesson 1',
            'content' => 'test lesson content 1',
            'type' => 'text'
        );
        $createdLesson2 = $this->getCourseService()->createLesson($lessonInfo2);
        $createdNote1 = $this->getNoteService()->saveNote(array(
            'content' => 'note_content1',
            'lessonId' => $createdLesson1['id'],
            'courseId' => $createdCourse['id']
        ));

        $createdNote2 = $this->getNoteService()->saveNote(array(
            'content' => 'note_content1',
            'lessonId' => $createdLesson2['id'],
            'courseId' => $createdCourse['id']
        ));

        $searchedNotes = $this->getNoteService()->searchNotes(
            array('courseId'=>$createdCourse['id']),
            array('createdTime' => 'DESC'), 0, 30);
        $this->assertEquals(2, count($searchedNotes));
        $this->assertContains($createdNote2, $searchedNotes);
    }

    public function testSaveNote()
    {
        $courseInfo = array(
            'type' => 'online',
            'title' => 'online test course'
        );
        $createdCourse = $this->getCourseService()->createCourse($courseInfo);
        $lessonInfo = array(
            'courseId' => $createdCourse['id'],
            'title' => 'test lesson 1',
            'content' => 'test lesson content 1',
            'type' => 'text'
        );
        $createdLesson1 = $this->getCourseService()->createLesson($lessonInfo);
        $noteInfo = array(
            'content' => 'note_content',
            'lessonId' => $createdLesson1['id'],
            'courseId' => $createdCourse['id'],
        );
        $savedNote = $this->getNoteService()->saveNote($noteInfo);
        $this->assertNotNull($savedNote);

        $savedNote = $this->getNoteService()->saveNote( array('content' => 'Save Second Time','lessonId' => $createdLesson1['id'],'courseId' => $createdCourse['id']));
        $this->assertNotNull($savedNote);
    }

    public function testDeleteNote()
    {
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);

        $courseInfo = array(
            'type' => 'online',
            'title' => 'online test course'
        );
        $createdCourse = $this->getCourseService()->createCourse($courseInfo);
        $lessonInfo = array(
            'courseId' => $createdCourse['id'],
            'title' => 'test lesson 1',
            'content' => 'test lesson content 1',
            'type' => 'text'
        );
        $createdLesson1 = $this->getCourseService()->createLesson($lessonInfo);
        $noteInfo = array(
            'content' => 'note_content',
            'lessonId' => $createdLesson1['id'],
            'courseId' => $createdCourse['id'],
            'userId'=>$registeredUser['id']
        );
        $createdNote = $this->getNoteService()->saveNote($noteInfo);
        $result = $this->getNoteService()->deleteNote($createdNote['id']);
        $this->assertNull($result);
    }

     /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testDeleteNoteWithNotExist()
    {
        $this->getNoteService()->deleteNote(999);
    }


    public function testDeleteNotes()
    {   
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $courseInfo = array(
            'type' => 'online',
            'title' => 'online test course'
        );
        $createdCourse = $this->getCourseService()->createCourse($courseInfo);
        $lessonInfo1 = array(
            'courseId' => $createdCourse['id'],
            'title' => 'test lesson 1',
            'content' => 'test lesson content 1',
            'type' => 'text'
        );
        $createdLesson1 = $this->getCourseService()->createLesson($lessonInfo1);
        $lessonInfo2 = array(
            'courseId' => $createdCourse['id'],
            'title' => 'test lesson 1',
            'content' => 'test lesson content 1',
            'type' => 'text'
        );
        $createdLesson2 = $this->getCourseService()->createLesson($lessonInfo2);
        $createdNote1 = $this->getNoteService()->saveNote(array(
            'content' => 'note_content1',
            'lessonId' => $createdLesson1['id'],
            'courseId' => $createdCourse['id']
        ));

        $createdNote2 = $this->getNoteService()->saveNote(array(
            'content' => 'note_content1',
            'lessonId' => $createdLesson2['id'],
            'courseId' => $createdCourse['id'],
            'userId'=>$registeredUser['id']
        ));

        $ids = array($createdNote1['id'], $createdNote2['id']);
        $result = $this->getNoteService()->deleteNotes($ids);
        $this->assertNull($result);

    }

    protected function getNoteService()
    {
        return $this->getServiceKernel()->createService('Course.NoteService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
}