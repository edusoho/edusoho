<?php

namespace Biz\WrongBook\Pool;

use Biz\Activity\Service\ActivityService;
use Biz\Task\Service\TaskService;
use Biz\WrongBook\Dao\WrongQuestionBookPoolDao;

class CoursePool extends AbstractPool
{
    public function getPoolTarget($report)
    {
        // TODO: Implement getPoolTarget() method.
    }

    public function prepareSceneIds($poolId, $conditions)
    {
        $pool = $this->getWrongQuestionBookPoolDao()->get($poolId);
        if (empty($pool) || 'course' != $pool['target_type']) {
            return [];
        }

        $sceneIds = [];
        if (!empty($conditions['courseId'])) {
            $sceneIds = $this->findSceneIdsByCourseId($conditions['courseId']);
        }

        if (!empty($conditions['courseMediaType'])) {
            $sceneIdsByCourseMediaType = $this->findSceneIdsByCourseMediaType($pool['target_id'], $conditions['courseMediaType']);
            $sceneIds = empty($sceneIds) ? $sceneIdsByCourseMediaType : array_intersect($sceneIds, $sceneIdsByCourseMediaType);
        }

        if (!empty($conditions['courseTaskId'])) {
            $sceneIdsByCourseTaskId = $this->findSceneIdsByCourseTaskId($conditions['courseTaskId']);
            $sceneIds = empty($sceneIds) ? $sceneIdsByCourseTaskId : array_intersect($sceneIds, $sceneIdsByCourseTaskId);
        }

        return $sceneIds;
    }

    public function findSceneIdsByCourseId($courseId)
    {
        $activityTestPapers = $this->getActivityService()->findActivitiesByCourseIdAndType($courseId, 'testpaper', true);
        $activityHomeWorks = $this->getActivityService()->findActivitiesByCourseIdAndType($courseId, 'homework', true);
        $activityExercises = $this->getActivityService()->findActivitiesByCourseIdAndType($courseId, 'exercise', true);
        $activates = array_merge($activityTestPapers, $activityHomeWorks, $activityExercises);

        return $this->generateSceneIds($activates);
    }

    public function findSceneIdsByCourseMediaType($targetId, $mediaType)
    {
        if (!in_array($mediaType, ['testpaper', 'homework', 'exercise'])) {
            return [];
        }

        $activates = $this->getActivityService()->findActivitiesByCourseSetIdAndType($targetId, $mediaType, true);

        return $this->generateSceneIds($activates);
    }

    public function findSceneIdsByCourseTaskId($courseTaskId)
    {
        $courseTask = $this->getCourseTaskService()->getTask($courseTaskId);
        if (empty($courseTask)) {
            return [];
        }

        return $this->findSceneIdsByCourseId($courseTask['courseId']);
    }

    protected function generateSceneIds($activates)
    {
        $sceneIds = [];
        array_walk($activates, function ($activity) use (&$sceneIds) {
            if (!empty($activity['ext'])) {
                $sceneIds[] = $activity['ext']['answerSceneId'];
            }
        });

        return $sceneIds;
    }

    /**
     * @return WrongQuestionBookPoolDao
     */
    protected function getWrongQuestionBookPoolDao()
    {
        return $this->biz->dao('WrongBook:WrongQuestionBookPoolDao');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return  $this->biz->service('Activity:ActivityService');
    }

    /**
     * @return TaskService
     */
    protected function getCourseTaskService()
    {
        return $this->biz->service('Task:TaskService');
    }
}
