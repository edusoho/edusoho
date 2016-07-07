<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class MyTaskController extends BaseController
{
    public function indexAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $conditions = array('userId' => $user['id']);

        $tasks = $this->getTaskService()->searchTasks($conditions, array('taskStartTime', 'ASC'), 0, PHP_INT_MAX);
        $tasksevents = array();

        if ($tasks) {
            foreach ($tasks as $key => $task) {
                $taskSames = $this->_prepareTaskDays($task);
                $tasksevents = array_merge($tasksevents, $taskSames);
            } /*else{//其他扩展任务url
        }*/
        } else {
            $tasksevents = array(array(
                'title' => '并没有任务',
                'start' => date("Y-m-d", time()),
                'end' => date("Y-m-d", time()),
            ));
        }

        $jsontasks = json_encode($tasksevents);

        return $this->render('TopxiaWebBundle:MyTask:index.html.twig', array(
            'user' => $user,
            'taskjson' => $jsontasks,
            'today' => date("Y-m-d", time()),
        ));
    }

    private function _prepareTaskDays($task)
    {
        $taskSames = array();
        $taskNew['title'] = $task['title'];
        $taskNew['start'] = date("Y-m-d H:i:s", $task['taskStartTime']);
        $taskNew['id'] = $task['id'];

        if ($task['status'] == 'completed') {
            $taskNew['color'] = '#46c37b';
        } else {
            $taskNew['color'] = '#919191';
        }

        if ($task['taskType'] == 'studyplan') {
            if ($task['targetType'] == 'homework') {
                $taskNew['url'] = $this->generateUrl('course_homework_start_do', array(
                    'courseId' => $task['meta']['courseId'], 'homeworkId' => $task['targetId']));
            } else {
                $taskNew['url'] = $this->generateUrl('course_learn', array(
                    'id' => $task['meta']['courseId'])) . '#lesson/' . $task['targetId'];
            }
        }

        $taskNew['end'] = date("Y-m-d H:i:s", strtotime('+1 day', $task['taskEndTime']));
        $dayDiff = ceil(($task['taskEndTime'] - $task['taskStartTime']) / (3600 * 24));

        if ($task['taskType'] == 'studyplan'
            && $this->isPluginInstalled('ClassroomPlan')
            && $dayDiff > 1) {

            $userPlanMember = $this->getClassroomPlanMemberService()->getPlanMemberByPlanId($task['batchId'], $task['userId']);
            //切割任务，把跨天的任务分割成每日单独的任务
            if ($userPlanMember && count($userPlanMember['metas']['availableDate']) < 7) {
                $taskNew['end'] = date("Y-m-d", strtotime('+1 day', $task['taskStartTime'])) . ' 23:59:59';

                for ($i = 1; $i < $dayDiff; $i++) {
                    $taskSame = $taskNew;

                    $week = date('w', strtotime("+{$i} day", strtotime($taskNew['start'])));
                    if (!in_array($week, $userPlanMember['metas']['availableDate'])) {
                        continue;
                    }
                    $taskSame['start'] = date('Y-m-d H:i:s', strtotime("+{$i} day", strtotime($taskNew['start'])));
                    $taskSame['end'] = date('Y-m-d H:i:s', strtotime("+{$i} day", strtotime($taskNew['end'])));

                    $taskSames[] = $taskSame;
                }
            }
        }

        $taskSames[] = $taskNew;

        return $taskSames;
    }

    protected function getTaskService()
    {
        return $this->getServiceKernel()->createService('Task.TaskService');
    }

    protected function getClassroomPlanMemberService()
    {
        return $this->getServiceKernel()->createService('ClassroomPlan:ClassroomPlan.ClassroomPlanMemberService');
    }
}
