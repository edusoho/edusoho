<?php

namespace AppBundle\Controller\Classroom;

use Biz\Task\Service\TaskService;
use Biz\Course\Service\CourseService;
use AppBundle\Controller\BaseController;
use Biz\Classroom\Service\ClassroomService;

class CourseTaskController extends BaseController
{
    public function buyHintAction($courseId)
    {
        $classroom = $this->getClassroomService()->getClassroomByCourseId($courseId);

        return $this->render('classroom/hint-modal.html.twig', array(
            'classroom' => $classroom,
        ));
    }

    public function listAction($classroomId, $courseId)
    {
        $user = $this->getCurrentUser();
        $member = $user['id'] ? $this->getClassroomService()->getClassroomMember($classroomId, $user['id']) : null;
        $course = $this->getCourseService()->getCourse($courseId);
        $course['courseItemNum'] = $this->getCourseService()->countCourseItems($course);

        return $this->render('classroom/course/tasks-list.html.twig', array(
            'classroomId' => $classroomId,
            'course' => $course,
            'member' => $member,
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
