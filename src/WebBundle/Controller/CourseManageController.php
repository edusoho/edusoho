<?php
/**
 * User: Edusoho V8
 */

namespace WebBundle\Controller;

use Biz\Course\Service\CourseService;
use Biz\Task\Service\TaskService;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;

class CourseManageController extends BaseController
{
    public function tasksAction(Request $request, $courseId)
    {
        $course      = $this->getCourseService()->tryManageCourse($courseId);
        $tasks       = $this->getTaskService()->findUserTasksFetchActivityAndResultByCourseId($courseId);
        $courseItems = $this->getCourseService()->getCourseItems($courseId);

        return $this->render('WebBundle:CourseManage:task-list.html.twig', array(
            'tasks'  => $tasks,
            'course' => $course,
            'items'  => $courseItems
        ));
    }

    public function courseItemsSortAction(Request $request, $courseId)
    {
        $ids = $request->request->get("ids");
        $this->getCourseService()->sortCourseItems($courseId, $ids);
        return $this->createJsonResponse(array('result' => true));
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }
}