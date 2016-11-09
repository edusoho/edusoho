<?php
namespace WebBundle\Controller;

use Biz\Activity\Service\Impl\ActivityServiceImpl;
use Biz\Task\Service\TaskService;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Course\Impl\CourseServiceImpl;

class TaskController extends BaseController
{
    public function showAction(Request $request, $courseId, $id)
    {
        $task     = $this->tryLearnTask($courseId, $id);
        $tasks    = $this->getCourseTasks($courseId);
        $activity = $this->getActivityService()->getActivity($task['activityId']);

        return $this->render('WebBundle:Task:show.html.twig', array(
            'task'     => $task,
            'tasks'    => $tasks,
            'activity' => $activity,
            'types'    => $this->getActivityService()->getActivityTypes()
        ));
    }

    public function taskActivityAction(Request $request, $courseId, $id)
    {
        $task = $this->tryLearnTask($courseId, $id);

        return $this->forward('WebBundle:Activity:show', array(
            'id'       => $task['activityId'],
            'taskId'   => $task['id'],
            'courseId' => $courseId
        ));
    }

    public function triggerAction(Request $request, $courseId, $id, $eventName)
    {
        $task         = $this->tryLearnTask($courseId, $id);
        $data         = $request->request->all();
        $data['task'] = $task;

        return $this->forward('WebBundle:Activity:trigger', array(
            'id'        => $task['activityId'],
            'eventName' => $eventName,
            'data'      => $data
        ));
    }

    public function finishAction(Request $request, $courseId, $id)
    {
    }

    public function playerAction(Request $request, $courseId, $taskId)
    {
        $task     = $this->tryLearnTask($courseId, $taskId);
        $activity = $this->getActivityService()->getActivity($task['activityId']);
        if (empty($activity)) {
            $this->createResourceNotFoundException('activity', $task['activityId']);
        }
        $context = array();
        return $this->forward('TopxiaWebBundle:Player:show', array(
            'id'      => $activity['ext']["mediaId"],
            'context' => $context
        ));
    }

    protected function tryLearnTask($courseId, $taskId)
    {
        $this->getCourseService()->tryLearnCourse($courseId);
        $task = $this->getTaskService()->getTask($taskId);

        if (empty($task)) {
            throw $this->createResourceNotFoundException('task', $taskId);
        }

        if ($task['courseId'] != $courseId) {
            throw $this->createAccessDeniedException();
        }
        return $task;
    }

    protected function getCourseTasks($courseId)
    {
        //列举course下的所有tasks，并：
        //1. 标记任务的进度（course_task_result.status: ''=未开始，start=进行中，finish=已完成 ）
        //2. 如果有length的需表达其长度，目前主要是视频（hh:ii:ss）
        //3. 标记活动的类型（icon + name）
        $tasks = $this->getTaskService()->findTasksByCourseId($courseId);
        if (empty($tasks)) {
            return $tasks;
        }
        $taskResults = $this->getTaskService()->findTaskResultsByCourseId($courseId, $this->getUser()->getId());
        if (!empty($taskResults)) {
            foreach ($taskResults as $tr) {
                foreach ($tasks as $tk => $t) {
                    if ($tr['courseTaskId'] != $t['id']) {
                        continue;
                    }
                    if (!isset($t['task_result']) || !$t['task_result']['status'] == 'finish') {
                        $tasks[$tk]['task_result'] = $tr;
                        break;
                    }
                }
            }
        }
        $activityConfigs = $this->getActivityService()->getActivityTypes();
        $activities      = $this->getActivityService()->getActivities(array_column($tasks, 'activityId'));
        $activityMap     = array();
        foreach ($activities as $act) {
            $activityMap[$act['id']] = $act;
        }
        foreach ($tasks as $tk => $t) {
            $act                         = $activityMap[$t['activityId']];
            $config                      = $activityConfigs[$act['mediaType']];
            $tasks[$tk]['activity_meta'] = array_merge($config->getMetas(), array('length' => $this->formatActivityLength($act['length'])));
        }

        return $tasks;
    }

    protected function formatActivityLength($len)
    {
        if (empty($len) || $len == 0) {
            return null;
        }
        $h = floor($len / 60);
        $m = fmod($len, 60);
        //TODO 目前没考虑秒
        return ($h < 10 ? '0'.$h : $h).':'.($m < 10 ? '0'.$m : $m).':00';
    }

    /**
     * @return CourseServiceImpl
     */
    protected function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course.CourseService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return ActivityServiceImpl
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }
}
