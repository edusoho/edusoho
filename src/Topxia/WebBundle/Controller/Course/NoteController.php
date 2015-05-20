<?php
namespace Topxia\WebBundle\Controller\Course;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class NoteController extends BaseController
{
    public function listAction(Request $request, $courseIds, $filters)
    {
       $user = $this->getCurrentUser();

        $conditions = $this->convertFiltersToConditions($courseIds, $filters);

        $paginator = new Paginator(
            $request,
            $this->getNoteService()->searchNoteCount($conditions),
            20
        );
        $orderBy = $this->convertFiltersToOrderBy($filters);

        $notes = $this->getNoteService()->searchNotes(
            $conditions,
            $orderBy,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $noteLikes = $this->getNoteService()->findNoteLikesByNoteIdsAndUserId(ArrayToolkit::column($notes, 'id'), $user['id']);
        $userIds = ArrayToolkit::column($notes, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);
        $courseIds = ArrayToolkit::column($notes, 'courseId');
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);

        return $this->render('TopxiaWebBundle:Course\Note:notes-list.html.twig', array(
            'notes' => $notes,
            'noteLikes' => $noteLikes,
            'users' => $users,
            'paginator' => $paginator,
            'courses' => $courses,
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

    private function convertFiltersToConditions($courseIds, $filters)
    {
        $conditions = array(
            'status' => 1,
        );

        if (is_numeric($courseIds)) {
            $conditions['courseId'] = $courseIds;
        }

        if (!empty($filters['courseId'])) {
            $conditions['courseId'] = $filters['courseId'];
        }

        if (is_array($courseIds) && empty($filters['courseId'])) {
            $conditions['courseIds'] = $courseIds;
        }

        return $conditions;
    }

    private function convertFiltersToOrderBy($filters)
    {
        $orderBy = array();
        switch ($filters['sort']) {
            case 'latest':
                $orderBy['updatedTime'] = 'DESC';
                break;
            case 'likeNum':
                $orderBy['likeNum'] = 'DESC';
                break;
            default:
                $orderBy['updatedTime'] = 'DESC';
                break;
        }
        return $orderBy;
    }

    private function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

    private function getNoteService()
    {
        return $this->getServiceKernel()->createService('Course.NoteService');
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}
