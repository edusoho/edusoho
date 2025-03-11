<?php

namespace Biz\StudyPlan\Service\Impl;

use Biz\Activity\Service\ActivityService;
use Biz\BaseService;
use Biz\Course\Service\CourseService;
use Biz\StudyPlan\Factory\CalculationStrategyFactory;
use Biz\StudyPlan\Service\StudyPlanService;
use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;

class StudyPlanServiceImpl extends BaseService implements StudyPlanService
{
    public function generate($startTime, $endTime, $studyDays, $courseId)
    {
        $activities = $this->getActivityLearnTime($courseId);
        // 获取学习全部任务时间
        $totalStudyTime = array_sum(array_column($activities, 'learnTime'));
        // 计算全部可学习天数
        $learnTotalDay = $this->getLearnTotalDay($startTime, $endTime, $studyDays);
        // 计算每天学多长时间
        $learnTimePerDay = ceil($totalStudyTime / $learnTotalDay);
        // 每天学习时长 / 每天每个任务学习时长 = 每天学习几个任务
        $waitLearnTasks = $this->getTaskService()->searchTasks(['' => ''], [], 0, PHP_INT_MAX);
        // 构建 activityId => learnTime 的映射
        $activityMap = [];
        foreach ($activities as $activity) {
            $activityMap[$activity['id']] = $activity['learnTime'];
        }

        // 填充 learnTime 到任务中
        foreach ($waitLearnTasks as &$task) {
            $activityId = $task['activityId'];
            $task['learnTime'] = $activityMap[$activityId] ?? 0; // 处理未找到的情况
        }
        unset($task); // 重要：清除引用
        $studyPlan = $this->generateStudyPlan($learnTimePerDay, $waitLearnTasks);
    }

    protected function getActivityLearnTime($courseId)
    {
        $taskResults = $this->getTaskResultService()->findUserFinishedTaskResultsByCourseId($courseId);
        $conditions = ['fromCourseId' => $courseId];
        if (!empty($taskResults)) {
            $conditions['excludeIds'] = array_column($taskResults, 'activityId');
        }
        $activities = $this->getActivityService()->search(
            $conditions,
            [],
            0,
            PHP_INT_MAX['id']
        );
        $activities = $this->getActivityService()->findActivities(array_column($activities, 'id'), true);
        foreach ($activities as &$activity) {
            $activity['learnTime'] = CalculationStrategyFactory::create($activity)->calculateTime($activity);
        }

        return $activities;
    }

    public function getLearnTotalDay($startTime, $endTime, $studyDays)
    {
        $utcTimezone = new \DateTimeZone('UTC');

        // 创建UTC日期对象并设置时间为00:00:00
        $startDate = \DateTime::createFromFormat('U', $startTime, $utcTimezone)->setTime(0, 0);
        $endDate = \DateTime::createFromFormat('U', $endTime, $utcTimezone)->setTime(0, 0);

        if ($startDate > $endDate) {
            return 0;
        }

        $totalDays = 0;
        foreach ($studyDays as $targetDay) {
            // 计算第一个目标日
            $startDayOfWeek = (int) $startDate->format('N');
            $diffStart = ($targetDay - $startDayOfWeek + 7) % 7;
            $firstOccurrence = (clone $startDate)->modify("+{$diffStart} days");

            // 若第一个目标日超过结束日期，跳过
            if ($firstOccurrence > $endDate) {
                continue;
            }

            // 计算最后一个目标日
            $endDayOfWeek = (int) $endDate->format('N');
            $diffEnd = ($endDayOfWeek - $targetDay + 7) % 7;
            $lastOccurrence = (clone $endDate)->modify("-{$diffEnd} days");

            // 计算间隔天数并统计次数
            $daysBetween = $lastOccurrence->diff($firstOccurrence)->days;
            $totalDays += floor($daysBetween / 7) + 1;
        }

        return $totalDays;
    }

    protected function generateStudyPlan(int $dailyTime, array $tasks): array
    {
        // 检查任务时间是否合法
        foreach ($tasks as $task) {
            if ($task['time'] > $dailyTime) {
                throw new InvalidArgumentException("任务 {$task['id']} 时间超过每日上限");
            }
        }

        // 按任务时间降序排序（关键优化）
        usort($tasks, function ($a, $b) {
            return $b['time'] <=> $a['time'];
        });

        $days = [];
        foreach ($tasks as $task) {
            $allocated = false;

            // 尝试将任务放入已有的天数
            foreach ($days as &$day) {
                if ($day['remaining'] >= $task['time']) {
                    $day['tasks'][] = [
                        'id' => $task['id'],
                        'time' => $task['time'],
                    ];
                    $day['remaining'] -= $task['time'];
                    $allocated = true;
                    break;
                }
            }

            // 无法放入则创建新天数
            if (!$allocated) {
                $days[] = [
                    'tasks' => [
                        [
                            'id' => $task['id'],
                            'time' => $task['time'],
                        ],
                    ],
                    'remaining' => $dailyTime - $task['time'],
                ];
            }
        }

        return $days;
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return ActivityService
     */
    private function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }
}
