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
    public function generate($startTime, $endTime)
    {
        // TODO: Implement generate() method.
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
                    $calculationFactory = CalculationStrategyFactory::create($activity);
                    $totalTime += $calculationFactory->calculateTime($activity);
                }
            } catch (\InvalidArgumentException $e) {
                // 处理未知类型或记录日志
            }
        }

        return $totalTime;
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
