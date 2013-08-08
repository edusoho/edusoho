<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class MyNoteController extends BaseController
{
    public function notesDetailAction(Request $request, $userId, $courseId)
    {   
        $courseNotes = $this->getNoteService()->findUserCourseNotes($userId, $courseId);
        $course = $this->getCourseService()->getCourse($courseId);
        $lessons = $this->getCourseService()->getCourseLessons($courseId);
        $lessons = ArrayToolkit::index($lessons, 'number');
        $courseNotes = ArrayToolkit::index($courseNotes, 'lessonId');

        return $this->render('TopxiaWebBundle:MyNote:my-notes-detail.html.twig',
            array('courseNotes'=>$courseNotes,
                'course'=>$course,
                'lessons'=>$lessons));
    }

    public function deleteNoteAction(Request $request, $noteId)
    {
        $note = $this->getNoteService()->getNote($noteId);
        $url = '/course/'.$note['courseId'].'/learn#/lesson/'.$note['lessonId'];
        $result = $this->getNoteService()->deleteNote($noteId);
        if($result == 1){
            return $this->createJsonResponse(array('status'=>'ok', 'id' => $noteId, 'url'=>$url, 'lessonId'=>$note['lessonId']));
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
            $this->getCourseService()->searchMemberCount(array(
                "userId"=>$user['id'],
                "role"=>"student",
                "notesNumGreaterThan"=>0)),
            5
        );
        $courseMembers = $this->getCourseService()->searchMember(
            array(
                "userId"=>$user['id'],
                "role"=>"student",
                "notesNumGreaterThan"=>0),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $courseMembers = ArrayToolkit::index($courseMembers, 'courseId');
        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($courseMembers, 'courseId'));
        return $this->render('TopxiaWebBundle:MyNote:my-notes.html.twig',
            array(
                'courseMembers'=>$courseMembers,
                'paginator' => $paginator,
                'courses'=>$courses));
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