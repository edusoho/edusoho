<?php

namespace Biz\Course\Accessor;

use Biz\Accessor\AccessorAdapter;
use Biz\Accessor\AccessorInterface;
use Biz\Activity\Service\ActivityService;
use Biz\Course\Service\CourseService;
use Biz\System\Service\SettingService;

class LearnCourseTaskAccessor extends AccessorAdapter
{
    public function access($task)
    {
        if (!$task) {
            return $this->buildResult('course.task.not_found');
        }

        $learnCourseChain = $this->biz['course.learn_chain'];
        $course = $this->getCourseService()->getCourse($task['courseId']);
        $courseLearnResult = $learnCourseChain->process($course);

        if ($courseLearnResult['code'] == AccessorInterface::SUCCESS) {
            return null;
        } else {
            return $this->tryFreeLearn($task, $course, $courseLearnResult['code']);
        }
    }

    private function tryFreeLearn($task, $course, $previousCode)
    {
        if ($this->canAnonymousPreview()) {
            if ($task['isFree']) {
                return null;
            }

            if ($course['tryLookable']
                && $task['type'] == 'video') {
                $activity = $this->getActivityService()->getActivity($task['activityId'], true);
                if (!empty($activity['ext']) && !empty($activity['ext']['file']) && $activity['ext']['file']['storage'] === 'cloud') {
                    return $this->buildResult('allow_trial');
                }
            }
        }

        return $this->buildResult($previousCode);
    }

    private function canAnonymousPreview()
    {
        return $this->getSettingService()->get('course.allowAnonymousPreview', 1);
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

    /**
     * @return SettingService
     */
    private function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }

    /**
     * @return ActivityService
     */
    private function getActivityService()
    {
        return $this->biz->service('Activity:ActivityService');
    }
}
