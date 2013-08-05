<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class MyNotesController extends BaseController
{
    public function notesDetailAction(Request $request, $userId, $courseId)
    {   
        $courseNotes = $this->getNoteService()->findUserCourseNotes($userId, $courseId);
        $course = $this->getCourseService()->getCourse($courseId);
        $lessons = $this->getCourseService()->findLessonsByIds(ArrayToolkit::column($courseNotes, 'lessonId'));
        return $this->render('TopxiaWebBundle:MyNotes:my-notes-detail.html.twig',
            array('courseNotes'=>$courseNotes,
                'course'=>$course,
                'lessons'=>$lessons));
    }

    public function deleteNoteAction(Request $request, $noteId)
    {

        $result = $this->getNoteService()->deleteNote($noteId);
        if($result == 1){
            return $this->createJsonResponse(array('status'=>'ok', 'id' => $noteId));
        } else {
            return $this->createJsonResponse(array('status'=>'error', 'id' => $noteId));
        }
    }

    public function updateNoteAction(Request $request, $noteId)
    {
        $content = $request->request->get('content');
        $updatedNote = $this->getNoteService()->updateNote($noteId, array('content'=>$content));
        if(!empty($updatedNote)){
            return $this->createJsonResponse(array('status'=>'ok', 'note'=>$updatedNote));
        } else {
            return $this->createJsonResponse(array('status'=>'error'));
        }
    }

    public function myNotesAction(Request $request)
    {

        $user = $this->getCurrentUser();
        
         $paginator = new Paginator(
            $request,
            $this->getNoteService()->searchNotesCount(array("userId"=>$user['id'])),
            5
        );

        $notes = $this->getNoteService()->searchNotes(
            array("userId"=>$user['id']),
            'update',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        
        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($notes, 'courseId'));

        foreach ($courses as $key => &$course) {
            $course['notes'] = array();
            foreach ($notes as $key => $note) {
                if($course['id'] == $note['courseId']){
                    $course['notes'][$note['id']] = $note;
                }
            }
        }

        foreach ($courses as $key => &$course) {
            $currentNotes = current($course['notes']);
            $course['noteLatestUpdatedTime'] = $currentNotes['updatedTime'];
            
        }

        $lessons = $this->getCourseService()->findLessonsByIds(ArrayToolkit::column($notes, 'lessonId'));
        return $this->render('TopxiaWebBundle:MyNotes:my-notes.html.twig',
            array('myNotes'=>$notes,
                'courses'=>$courses,
                'lessons'=>$lessons,
                'paginator' => $paginator));
    }


    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Course.ThreadService');
    }

    protected function getNoteService()
    {
        return $this->getServiceKernel()->createService('Course.NoteService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

}