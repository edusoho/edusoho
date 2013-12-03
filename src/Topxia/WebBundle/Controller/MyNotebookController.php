<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class MyNotebookController extends BaseController
{

    public function indexAction(Request $request)
    {
        $user = $this->getCurrentUser();

        $conditions = array(
            'userId' => $user['id'],
            'noteNumGreaterThan' => 0
        );

        $paginator = new Paginator(
            $request,
            $this->getCourseService()->searchMemberCount($conditions),
            10
        );

        $courseMembers = $this->getCourseService()->searchMember($conditions, $paginator->getOffsetCount(), $paginator->getPerPageCount());

        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($courseMembers, 'courseId'));
        
        return $this->render('TopxiaWebBundle:MyNotebook:index.html.twig', array(
            'courseMembers'=>$courseMembers,
            'paginator' => $paginator,
            'courses'=>$courses
        ));
    }

    public function showAction(Request $request, $courseId)
    {   
        $user = $this->getCurrentUser();

        $course = $this->getCourseService()->getCourse($courseId);
        $lessons = ArrayToolkit::index($this->getCourseService()->getCourseLessons($courseId), 'id');
        $notes = $this->getNoteService()->findUserCourseNotes($user['id'], $course['id']);

        foreach ($notes as &$note) {
            $note['lessonNumber'] = empty($lessons[$note['lessonId']]) ? 0 : $lessons[$note['lessonId']]['number'];
            unset($note);
        }

        usort($notes, function($note1, $note2) {
            if ($note1['lessonNumber'] == 0) {
                return true;
            }

            if ($note2['lessonNumber'] == 0) {
                return false;
            }

            return $note1['lessonNumber'] > $note2['lessonNumber'];
        });

        return $this->render('TopxiaWebBundle:MyNotebook:show.html.twig', array(
            'course' => $course,
            'lessons' => $lessons,
            'notes' => $notes,
        ));
    }

    public function noteDeleteAction(Request $request, $id)
    {
        $this->getNoteService()->deleteNote($id);
        return $this->createJsonResponse(true);
    }

    protected function getNoteService()
    {
        return $this->getServiceKernel()->createService('Course.NoteService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

}