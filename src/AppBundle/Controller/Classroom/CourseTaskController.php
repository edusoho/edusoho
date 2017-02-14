<?php
namespace AppBundle\Controller\Classroom;

use Biz\Task\Service\TaskService;
use Biz\Course\Service\CourseService;
use AppBundle\Controller\BaseController;
use Biz\Classroom\Service\ClassroomService;
use Symfony\Component\HttpFoundation\Request;

class CourseTaskController extends BaseController
{
    public function previewAction(Request $request, $classroomId, $courseId)
    {
        $taskId    = $request->query->get('taskId', 0);
        $course    = $this->getCourseService()->getCourse($courseId);
        $task      = $this->getTaskService()->getTask($taskId);
        $classroom = $this->getClassroomService()->getClassroom($classroomId);
        $user      = $this->getCurrentUser();
        $member    = $user['id'] ? $this->getClassroomService()->getClassroomMember($classroom['id'], $user['id']) : null;

        if (!$user->isLogin()) {
            return $this->forward('AppBundle:CourseTask:preview', array(
                'courseId' => $courseId,
                'lessonId' => $taskId
            ));
        }

        if ($task['free'] || $course['tryLookable'] || ($member && !$member['locked'])) {
            return $this->forward('AppBundle:CourseTask:preview', array(
                'courseId' => $courseId,
                'lessonId' => $taskId
            ));
        }

        return $this->redirect($this->generateUrl('classroom_buy_hint', array('courseId' => $course["id"])));
    }

    public function buyHintAction($courseId)
    {
        $classroom = $this->getClassroomService()->getClassroomByCourseId($courseId);

        return $this->render('classroom/hint-modal.html.twig', array(
            'classroom' => $classroom
        ));
    }

    public function listAction($classroomId, $courseId)
    {
        $user   = $this->getCurrentUser();
        $member = $user['id'] ? $this->getClassroomService()->getClassroomMember($classroomId, $user['id']) : null;
        $course = $this->getCourseService()->getCourse($courseId);
        return $this->render('classroom/course/tasks-list.html.twig', array(
            'classroomId' => $classroomId,
            'course'      => $course,
            'member'      => $member
        ));
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return TaskService
     */
    private function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }
}
