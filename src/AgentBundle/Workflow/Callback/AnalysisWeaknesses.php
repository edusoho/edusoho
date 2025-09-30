<?php

namespace AgentBundle\Workflow\Callback;

use AgentBundle\Workflow\AbstractWorkflow;
use AgentBundle\Workflow\TaskScheduler;
use AgentBundle\Workflow\TaskTrait;
use AppBundle\Common\ArrayToolkit;

class AnalysisWeaknesses extends AbstractWorkflow
{
    use TaskTrait;

    public function execute($inputs)
    {
        $this->getAIService()->pushMessage([
            'domainId' => $inputs['domainId'],
            'userId' => $inputs['userId'],
            'contentType' => 'text',
            'content' => $this->makeMarkdown($inputs),
            'push' => [
                'userId' => $inputs['userId'],
                'title' => '小知老师帮你抓到以下薄弱知识点，速来学习～',
                'message' => '刚才的答题结果✍️已帮你分析出薄弱知识点，快来看看自己哪方面知识需要加强👉 ',
                'category' => 'todo',
                'extra' => [
                    'domainId' => $inputs['domainId'],
                    'to' => 'ai',
                ],
            ],
        ]);
        $this->addStudyPlanTasksIfNecessary($inputs);
    }

    private function makeMarkdown($inputs)
    {
        $user = $this->getUserService()->getUser($inputs['userId']);
        $markdown = "hi，{$user['nickname']}同学，恭喜完成答题，根据此次答题结果分析，当前掌握较为薄弱的知识点是：  \n";
        foreach ($inputs['keypoints'] as $key => $keypoint) {
            $seq = $key + 1;
            $markdown .= "{$seq}. $keypoint\n";
        }
        if (empty($inputs['documents'])) {
            return $markdown;
        }
        $markdown .= "\n推荐以下学习知识点的相关课程任务：  \n";
        $tasks = $this->getTaskService()->findTasksByActivityIds(array_column($inputs['documents'], 'extId'));
        foreach ($inputs['documents'] as $key => $document) {
            $seq = $key + 1;
            $markdown .= "* [任务{$seq}: {$document['name']}](/course/{$document['dataset']['extId']}/task/{$tasks[$document['extId']]['id']})\n";
        }

        return $markdown;
    }

    private function addStudyPlanTasksIfNecessary($inputs)
    {
        $activityGroup = [];
        foreach ($inputs['documents'] as $document) {
            $activityGroup[$document['dataset']['extId']] = $activityGroup[$document['dataset']['extId']] ?? [];
            $activityGroup[$document['dataset']['extId']][] = $document['extId'];
        }
        $taskScheduler = new TaskScheduler();
        foreach ($activityGroup as $courseId => $activityIds) {
            $plan = $this->getStudyPlanService()->getPlanByCourseIdAndUserId($courseId, $inputs['userId']);
            if (empty($plan)) {
                continue;
            }
            $conditions = ['ids' => $activityIds, 'status' => 'published', 'mediaTypes' => ['text', 'video', 'audio', 'live', 'doc', 'ppt', 'testpaper', 'replay']];
            $activities = $this->getActivityService()->search(
                $conditions,
                [],
                0,
                PHP_INT_MAX
            );
            $activities = $this->filterSchedulableActivities($activities);
            if (empty($activities)) {
                continue;
            }
            $activities = $this->getActivityService()->findActivities(array_column($activities, 'id'));
            $activities = array_column($activities, null, 'id');
            $addTasks = $this->getTaskService()->searchTasks(['courseId' => $courseId, 'status' => 'published', 'activityIds' => array_column($activities, 'id')], ['seq' => 'ASC'], 0, count($activities), ['id', 'activityId', 'title', 'type']);
            if (empty($addTasks)) {
                continue;
            }
            $addTasksIndex = array_column($addTasks, null, 'id');
            $planTasks = $this->getStudyPlanService()->searchPlanTasks(['planId' => $plan['id'], 'learned' => 0], ['studyDate' => 'ASC', 'id' => 'ASC'], 0, PHP_INT_MAX);
            $courseTasks = $this->getTaskService()->findTasksByIds(array_column($planTasks, 'taskId'));
            $courseTasks = array_column($courseTasks, null, 'id');
            $newTasks = [];
            foreach (ArrayToolkit::group($planTasks, 'taskId') as $taskId => $planTasksGroup) {
                $courseTask = $courseTasks[$taskId];
                $courseTask['duration'] = array_sum(array_column($planTasksGroup, 'targetDuration')) - array_sum(array_column($planTasksGroup, 'learnedDuration'));
                if (empty($addTasksIndex[$taskId])) {
                    $newTasks[] = $courseTask;
                } else {
                    $addTasksIndex[$taskId]['duration'] = $courseTask['duration'];
                }
            }
            foreach ($addTasks as &$task) {
                $activity = $activities[$task['activityId']];
                $task['duration'] = empty($addTasksIndex[$task['id']]['duration']) ? $this->calculateDuration($activity) : $addTasksIndex[$task['id']]['duration'];
            }
            $newTasks = array_merge($addTasks, $newTasks);
            $plan['dailyLearnDuration'] = $plan['dailyAvgTime'] / 60;
            $this->getStudyPlanService()->generatePlanTasks($plan['id'], $taskScheduler->schedule($plan, $newTasks));
        }
    }
}
