<?php
namespace AppBundle\Controller;

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
        return $this->render('task/show.html.twig', array(
            'course'     => $course,
            'task'       => $task,
            'taskResult' => $taskResult,
            'tasks'      => $tasks,
            'activity'   => $activity,
            'preview'    => $preview,
            'backUrl'    => $backUrl
        ));
    }

    public function taskActivityAction(Request $request, $courseId, $id)
    {
        $preview = $request->query->get('preview');
        $task    = $this->tryLearnTask($courseId, $id, $preview);

        return $this->forward('AppBundle:Activity/Activity:show', array(
            'id'       => $task['activityId'],
            'courseId' => $courseId
        ));
    }


    public function taskPluginsAction(Request $request, $courseId, $taskId)
    {
        $preview = $request->query->get('preview', false);

        $task = $this->tryLearnTask($courseId, $taskId);
        return $this->createJsonResponse(array(
            array(
                'code' => 'task-list',
                'name' => '课程',
                'icon' => 'es-icon-menu',
                'url'  => $this->generateUrl('course_task_show_plugin_task_list', array(
                    'courseId' => $courseId,
                    'taskId'   => $taskId,
                    'preview'  => $preview,
                ))
            ),
            array(
                'code' => 'note',
                'name' => '笔记',
                'icon' => 'es-icon-edit',
                'url'  => $this->generateUrl('course_task_plugin_note', array(
                    'courseId' => $courseId,
                    'taskId'   => $taskId
                ))
            )
//            array(
//                'code' => 'question',
//                'name' => '问答',
//                'icon' => 'es-icon-help',
//                'url' => 'TaskPluginQuestionController'
//            )
        ));
    }

    public function triggerAction(Request $request, $courseId, $id)
    {
        $this->getCourseService()->tryTakeCourse($courseId);

        $eventName = $request->request->get('eventName');
        if (empty($eventName)) {
            throw $this->createNotFoundException('task event is empty');
        }

        $data   = $request->request->get('data', array());
        $result = $this->getTaskService()->trigger($id, $eventName, $data);

        return $this->createJsonResponse(array(
            'event'  => $eventName,
            'data'   => $data,
            'result' => $result
        ));
    }

    public function finishAction(Request $request, $courseId, $id)
    {
        $result   = $this->getTaskService()->finishTask($id);
        $task     = $this->getTaskService()->getTask($id);
        $nextTask = $this->getTaskService()->getNextTask($id);
        $course   = $this->getCourseService()->getCourse($task['courseId']);
        $user = $this->getUser();
        $conditions = array(
            'courseId' => $task['courseId'],
            'userId' => $user['id'],
            'status' => 'finish'
        );

        $finishedCount = $this->getTaskResultService()->countTaskResult($conditions);

        $finishedRate = empty($course['taskCount'])? 0 : intval($finishedCount/$course['taskCount']*100);

        return $this->render('task/finish-result.html.twig', array(
            'result'   => $result,
            'task'     => $task,
            'nextTask' => $nextTask,
            'course'   => $course,
            'finishedRate' => $finishedRate
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

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    protected function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }

    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }
}
