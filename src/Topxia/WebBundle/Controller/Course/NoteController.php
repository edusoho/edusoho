<?php
namespace Topxia\WebBundle\Controller\Course;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class NoteController extends BaseController
{
    public function listAction(Request $request, $courseIds)
    {
        if (!is_array($courseIds)) {
            $courseIds = array($courseIds);
        }

        $user = $this->getCurrentUser();

        $conditions = array(
            'status' => 1,
            'courseIds' => $courseIds,
        );

        $paginator = new Paginator(
            $request,
            $this->getNoteService()->searchNoteCount($conditions),
            20
        );
        $orderBy = array(
            'likeNum' => 'DESC',
            'createdTime' => 'DESC',
        );
        $notes = $this->getNoteService()->searchNotes(
            $conditions,
            $orderBy,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $noteLikes = $this->getNoteService()->findNoteLikesByNoteIdsAndUserId(ArrayToolkit::column($notes, 'id'), $user['id']);
        $userIds = ArrayToolkit::column($notes, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('TopxiaWebBundle:Course\Note:notes-list.html.twig', array(
            'notes' => $notes,
            'noteLikes' => $noteLikes,
            'users' => $users,
        ));
    }

    public function likeAction(Request $request, $noteId)
    {
        $this->getNoteService()->like($noteId);
        $note = $this->getNoteService()->getNote($noteId);
        
        return $this->createJsonResponse($note);
    }

    public function cancelLikeAction(Request $request, $noteId)
    {

        $note =  $this->getNoteService()->cancelLike($noteId);
        $note = $this->getNoteService()->getNote($noteId);
        
        return $this->createJsonResponse($note);
    }

    private function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

    private function getNoteService()
    {
        return $this->getServiceKernel()->createService('Course.NoteService');
    }
}
