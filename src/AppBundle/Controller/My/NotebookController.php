<?php


namespace AppBundle\Controller\My;


use AppBundle\Controller\BaseController;
use Biz\Course\Service\CourseNoteService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\Task\Service\TaskService;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class NotebookController extends BaseController
{
    public function showAction(Request $request, $courseId)
    {
        $user = $this->getCurrentUser();

        $course = $this->getCourseService()->getCourse($courseId);

        $tasks = $this->getTaskService()->findTasksByCourseId($course['id']);
        $tasks = ArrayToolkit::index($tasks, 'id');

        $notes = $this->getNoteService()->findCourseNotesByUserIdAndCourseId($user['id'], $course['id']);

        $notes = array_map(function ($note) use($tasks){
            $note['taskNumber'] = empty($lessons[$note['taskId']]) ? 0 : $lessons[$note['taskId']]['number'];
            return $note;
        }, $notes);

        usort($notes, function ($note1, $note2) {
            if ($note1['taskNumber'] == 0) {
                return true;
            }

            if ($note2['taskNumber'] == 0) {
                return false;
            }

            return $note1['taskNumber'] > $note2['taskNumber'];
        });

        return $this->render('my/notebook/show.html.twig', array(
            'course' => $course,
            'tasks'  => $tasks,
            'notes'  => $notes
        ));
    }

    public function indexAction(Request $request)
    {
        $user = $this->getUser();

        $conditions = array(
            'userId'             => $user['id'],
            'noteNumGreaterThan' => 0.1
        );

        $paginator = new Paginator(
            $request,
            $this->getCourseMemberService()->countMembers($conditions),
            10
        );

        $courseMembers = $this->getCourseMemberService()->searchMembers($conditions, $orderBy = array(), $paginator->getOffsetCount(), $paginator->getPerPageCount());

        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($courseMembers, 'courseId'));
        $courses = ArrayToolkit::index($courses, 'id');

        return $this->render('my/notebook/index.html.twig', array(
            'courseMembers' => $courseMembers,
            'paginator'     => $paginator,
            'courses'       => $courses
        ));
    }

    public function deleteAction(Request $request, $id)
    {
        $this->getNoteService()->deleteNote($id);
        return $this->createJsonResponse(true);
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return CourseNoteService
     */
    protected function getNoteService()
    {
        return $this->createService('Course:CourseNoteService');
    }
}