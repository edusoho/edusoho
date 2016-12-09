<?php
namespace AppBundle\Controller;

use Biz\Task\Service\TaskService;
use Biz\Course\Service\CourseService;
use Biz\Activity\Service\ActivityService;
use Symfony\Component\HttpFoundation\Request;

class TaskController extends BaseController
{
    public function showAction(Request $request, $courseId, $id)
    {
        $preview = $request->query->get('preview');
        $task    = $this->tryLearnTask($courseId, $id, (bool)$preview);
        $course  = $this->getCourseService()->getCourse($task['courseId']);
        $tasks   = $this->getTaskService()->findUserTasksFetchActivityAndResultByCourseId($courseId);

        $activity = $this->getActivityService()->getActivity($task['activityId']);

        if ($this->getCourseService()->isCourseStudent($courseId, $this->getUser()->getId())) {
            $backUrl = $this->generateUrl('course_set_show', array('id' => $activity['fromCourseSetId']));
        } else {
            $backUrl = $this->generateUrl('course_set_manage_course_tasks', array('courseSetId' => $activity['fromCourseSetId'], 'courseId' => $activity['fromCourseId']));
        }

        if (empty($activity)) {
            throw $this->createNotFoundException("activity not found");
        }

        $this->getActivityService()->trigger($activity['id'], 'start', array(
            'task' => $task
        ));

        $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId($id);
        return $this->render('WebBundle:Task:show.html.twig', array(
            'course'     => $course,
            'task'       => $task,
            'taskResult' => $taskResult,
            'tasks'      => $tasks,
            'activity'   => $activity,
            'preview'    => $preview,
            'types'      => $this->getActivityService()->getActivityTypes(),
            'backUrl'    => $backUrl
        ));
    }

    public function taskActivityAction(Request $request, $courseId, $id)
    {
        $preview = $request->query->get('preview');
        $task    = $this->tryLearnTask($courseId, $id, $preview);

        return $this->forward('WebBundle:Activity:show', array(
            'id'       => $task['activityId'],
            'courseId' => $courseId
        ));
    }

    public function triggerAction(Request $request, $courseId, $taskId)
    {
        $this->getCourseService()->tryTakeCourse($courseId);

        $eventName = $request->request->get('eventName');
        if (empty($eventName)) {
            throw $this->createNotFoundException('task event is empty');
        }

        $data   = $request->request->get('data', array());
        $result = $this->getTaskService()->trigger($taskId, $eventName, $data);

        return $this->createJsonResponse(array(
            'event'  => $eventName,
            'data'   => $data,
            'result' => $result
        ));
    }

    public function finishAction(Request $request, $courseId, $taskId)
    {
        $result   = $this->getTaskService()->finishTask($taskId);
        $task     = $this->getTaskService()->getTask($taskId);
        $nextTask = $this->getTaskService()->getNextTask($taskId);
        return $this->render('WebBundle:Task:finish-result.html.twig', array(
            'result'   => $result,
            'task'     => $task,
            'nextTask' => $nextTask
        ));
    }

    protected function tryLearnTask($courseId, $taskId, $preview = false)
    {
        if ($preview) {
            list($course, $member) = $this->getCourseService()->tryTakeCourse($courseId);
            //TODO先注释掉这段代码，学员的逻辑现在有问题，无法判断是否老师，完善后在开发
            /*if ($member['role'] != 'teacher' || $course['status'] != 'published') {
            throw $this->createAccessDeniedException('you are  not allowed to learn the task ');
            }*/
            $task = $this->getTaskService()->getTask($taskId);
        } else {
            $this->getCourseService()->tryTakeCourse($courseId);
            $task = $this->getTaskService()->tryTakeTask($taskId);
        }

        if (empty($task)) {
            throw $this->createResourceNotFoundException('task', $taskId);
        }

        if ($task['courseId'] != $courseId) {
            throw $this->createAccessDeniedException();
        }
        return $task;
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    protected function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }
}
