<?php

namespace Biz\StudyPlan\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\BaseService;
use Biz\Course\Service\CourseService;
use Biz\StudyPlan\Factory\CalculationStrategyFactory;
use Biz\StudyPlan\Service\StudyPlanService;
use Biz\Task\Service\TaskResultService;

class StudyPlanServiceImpl extends BaseService implements StudyPlanService
{
    public function generate($startTime, $endTime, $studyDays, $courseId)
    {
        list($notLearnTaskCount, $notLearnTotalTime) = $this->getActivityNotLearnTotalTime($courseId);
//        $timePerTask = ceil($notLearnTotalTime / $notLearnTaskCount);
        $totalStudyDay = $this->getTotalStudyDay($startTime, $endTime, $studyDays);
        $learnPerDay = ceil($notLearnTaskCount / $totalStudyDay);
        $learnTotalDay = $this->getLearnTotalDay($startTime, $endTime, $studyDays);
        // 获取每天学多长时间
        $notLearnTotalTime / $learnTotalDay;
        // 每天学习时长 / 每天每个任务学习时长 = 每天学习几个任务

        //获取所有没学的任务，创建学习记录，
    }

    protected function getActivityNotLearnTotalTime($courseId)
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
        $activitiesGroups = ArrayToolkit::group($activities, 'mediaType');
        $totalTime = 0;
        foreach ($activitiesGroups as $mediaType => $group) {
            try {
                foreach ($group as $activity) {
                    $totalTime += CalculationStrategyFactory::create($activity)->calculateTime($activity);
                }
            } catch (\InvalidArgumentException $e) {
                // 处理未知类型或记录日志
            }
        }

        return [count($activities), $totalTime];
    }

    public function getLearnTotalDay($startTime, $endTime, $studyDays, $courseId)
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
}
