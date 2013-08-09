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
        $courseInfo = array(
            'type' => 'online',
            'title' => 'online test course'
        );
        $createdCourse = $this->getCourseService()->createCourse($courseInfo);
        $lessonInfo = array(
            'courseId' => $createdCourse['id'],
            'title' => 'test lesson 1',
            'content' => 'test lesson content 1',
        );
        $createdLesson = $this->getCourseService()->createLesson($lessonInfo);

        $noteInfo = array(
            'content'=>'note_content',
            'lessonId'=>$createdLesson['id'],
            'courseId'=>$createdCourse['id']
        );
        $createdNote = $this->getNoteService()->addNote($noteInfo);
        $foundedNote = $this->getNoteService()->getNote($createdNote['id']);

        $this->assertEquals($currentUser['id'], $foundedNote['userId']);
        $this->assertEquals($noteInfo['courseId'], $foundedNote['courseId']);
        $this->assertEquals($noteInfo['lessonId'], $foundedNote['lessonId']);
        $this->assertEquals($noteInfo['content'], $foundedNote['content']);
        $this->assertEquals(0, $foundedNote['status']);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testGetNoteWithNotExistNote()
    {
        $this->getNoteService()->getNote(999);
    }

    public function testAddNote()
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
        );
        $createdLesson = $this->getCourseService()->createLesson($lessonInfo);
        $noteInfo = array(
            'content'=>'note_content',
            'lessonId'=>$createdLesson['id'],
            'courseId'=>$createdCourse['id']
        );
        $createdNote = $this->getNoteService()->addNote($noteInfo);
        $this->assertNotNull($createdNote);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testAddNoteWithNotExistCourse()
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
        );
        $createdLesson = $this->getCourseService()->createLesson($lessonInfo);
        $noteInfo = array(
            'content' => 'note_content',
            'lessonId' => $createdLesson['id'],
            'courseId' => 999
        );
        $this->getNoteService()->addNote($noteInfo);
    }

    /**
    * @expectedException Topxia\Service\Common\ServiceException
    */
    public function testAddNoteWithNotExistLesson()
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
        );
        $createdLesson = $this->getCourseService()->createLesson($lessonInfo);
        $noteInfo = array(
            'content' => 'note_content',
            'lessonId' => 999,
            'courseId' => $createdCourse['id']
        );
        $this->getNoteService()->addNote($noteInfo);
    }

    /**
    * @expectedException Topxia\Service\Common\ServiceException
    */
    public function testAddNoteTwice()
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
        );
        $createdLesson = $this->getCourseService()->createLesson($lessonInfo);
        $noteInfo = array(
            'content' => 'note_content',
            'lessonId' => $createdLesson['id'],
            'courseId' => $createdCourse['id']
        );
        $this->getNoteService()->addNote($noteInfo);
        $this->getNoteService()->addNote($noteInfo);
    }

    public function testUpdateNote()
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
        );
        $createdLesson1 = $this->getCourseService()->createLesson($lessonInfo);
        $createdLesson2 = $this->getCourseService()->createLesson($lessonInfo);
        $noteInfo = array(
            'content' => 'note_content',
            'lessonId' => $createdLesson1['id'],
            'courseId' => $createdCourse['id']
        );
        $createdNote = $this->getNoteService()->addNote($noteInfo);
        $updateInfo = array('content'=>'updated content');
        $updateNote = $this->getNoteService()->updateNote($createdNote['id'], $updateInfo);
        $this->assertEquals($updateInfo['content'], $updateNote['content']);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testUpdateNoteWithNotExistNote()
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
        );
        $createdLesson1 = $this->getCourseService()->createLesson($lessonInfo);
        $noteInfo = array(
            'content' => 'note_content',
            'lessonId' => $createdLesson1['id'],
            'courseId' => $createdCourse['id']
        );
        $createdNote = $this->getNoteService()->addNote($noteInfo);
        $updateInfo = array('content'=>'updated content');
        $this->getNoteService()->updateNote(999, $updateInfo);
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
        );
        $createdLesson1 = $this->getCourseService()->createLesson($lessonInfo);
        $noteInfo = array(
            'content' => 'note_content',
            'lessonId' => $createdLesson1['id'],
            'courseId' => $createdCourse['id']
        );
        $savedNote = $this->getNoteService()->saveNote($noteInfo);
        $this->assertNotNull($savedNote);

        $savedNote = $this->getNoteService()->saveNote( array('content' => 'Save Second Time','lessonId' => $createdLesson1['id'],'courseId' => $createdCourse['id']));
        $this->assertNotNull($savedNote);
    }

    /**
     * @group current
     */
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
        );
        $createdLesson1 = $this->getCourseService()->createLesson($lessonInfo);
        $noteInfo = array(
            'content' => 'note_content',
            'lessonId' => $createdLesson1['id'],
            'courseId' => $createdCourse['id'],
            'userId'=>$registeredUser['id']
        );
        $createdNote = $this->getNoteService()->addNote($noteInfo);
        $result = $this->getNoteService()->deleteNote($createdNote['id']);

        $this->assertEquals(1, $result);
    }

     /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testDeleteNoteWithNotExist()
    {
        $this->getNoteService()->deleteNote(999);
    }

    public function testGetUserLessonNote()
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
        );
        $createdLesson1 = $this->getCourseService()->createLesson($lessonInfo1);
        $noteInfo = array(
            'content' => 'note_content1',
            'lessonId' => $createdLesson1['id'],
            'courseId' => $createdCourse['id']
        );
        $createdNote = $this->getNoteService()->addNote($noteInfo);
        $note = $this->getNoteService()->findUserLessonNotes($user['id'], $createdLesson1['id']);

        $this->assertEquals($user['id'], $note['userId']);
        $this->assertEquals($noteInfo['courseId'], $note['courseId']);
        $this->assertEquals($noteInfo['lessonId'], $note['lessonId']);
        $this->assertEquals($noteInfo['content'], $note['content']);
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
        );
        $createdLesson1 = $this->getCourseService()->createLesson($lessonInfo1);
        $lessonInfo2 = array(
            'courseId' => $createdCourse['id'],
            'title' => 'test lesson 1',
            'content' => 'test lesson content 1',
        );
        $createdLesson2 = $this->getCourseService()->createLesson($lessonInfo2);
        $createdNote1 = $this->getNoteService()->addNote(array(
            'content' => 'note_content1',
            'lessonId' => $createdLesson1['id'],
            'courseId' => $createdCourse['id']
        ));

        $createdNote2 = $this->getNoteService()->addNote(array(
            'content' => 'note_content1',
            'lessonId' => $createdLesson2['id'],
            'courseId' => $createdCourse['id'],
            'userId'=>$registeredUser['id']
        ));

        $ids = array($createdNote1['id'], $createdNote2['id']);
        $result = $this->getNoteService()->deleteNotes($ids);
        $this->assertTrue($result);

    }

    public function testsearchNotesCount()
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
        );
        $createdLesson1 = $this->getCourseService()->createLesson($lessonInfo1);
        $lessonInfo2 = array(
            'courseId' => $createdCourse['id'],
            'title' => 'test lesson 1',
            'content' => 'test lesson content 1',
        );
        $createdLesson2 = $this->getCourseService()->createLesson($lessonInfo2);
        $createdNote1 = $this->getNoteService()->addNote(array(
            'content' => 'note_content1',
            'lessonId' => $createdLesson1['id'],
            'courseId' => $createdCourse['id']
        ));

        $createdNote2 = $this->getNoteService()->addNote(array(
            'content' => 'note_content1',
            'lessonId' => $createdLesson2['id'],
            'courseId' => $createdCourse['id']
        ));

        $resultCount = $this->getNoteService()->searchNotesCount(array('courseId'=>$createdCourse['id'], 'lessonId'=>$createdLesson2['id']));
        $this->assertEquals(1, $resultCount);
        $resultCount = $this->getNoteService()->searchNotesCount(array('courseId'=>$createdCourse['id']));
        $this->assertEquals(2, $resultCount);
    }

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
        );
        $createdLesson1 = $this->getCourseService()->createLesson($lessonInfo1);
        $lessonInfo2 = array(
            'courseId' => $createdCourse['id'],
            'title' => 'test lesson 1',
            'content' => 'test lesson content 1',
        );
        $createdLesson2 = $this->getCourseService()->createLesson($lessonInfo2);
        $createdNote1 = $this->getNoteService()->addNote(array(
            'content' => 'note_content1',
            'lessonId' => $createdLesson1['id'],
            'courseId' => $createdCourse['id']
        ));

        $createdNote2 = $this->getNoteService()->addNote(array(
            'content' => 'note_content1',
            'lessonId' => $createdLesson2['id'],
            'courseId' => $createdCourse['id']
        ));

        $searchedNotes = $this->getNoteService()->searchNotes(
            array('courseId'=>$createdCourse['id']),
            'created', 0, 30);
        $this->assertEquals(2, count($searchedNotes));
        $this->assertContains($createdNote2, $searchedNotes);
    }

    private function getNoteService()
    {
        return $this->getServiceKernel()->createService('Course.NoteService');
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