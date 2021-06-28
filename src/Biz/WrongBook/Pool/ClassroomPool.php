<?php

namespace Biz\WrongBook\Pool;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseSetService;
use Biz\Task\Service\TaskService;
use Biz\WrongBook\Dao\WrongQuestionBookPoolDao;

class ClassroomPool extends AbstractPool
{
    public function getPoolTarget($report)
    {
        // TODO: Implement getPoolTarget() method.
    }

    public function prepareSceneIds($poolId, $conditions)
    {
        $pool = $this->getWrongQuestionBookPoolDao()->get($poolId);
        if (empty($pool)) {
            return [];
        }

        $sceneIds = [];
        if (!empty($conditions['classroomCourseSetId'])) {
            $sceneIds['sceneIds'] = $this->findSceneIdsByClassroomCourseSetId($conditions['classroomCourseSetId']);
        }

        if (!empty($conditions['classroomMediaType'])) {
            $sceneIdsByClassroomMediaType = $this->findSceneIdsByClassroomMediaType($pool['target_id'], $conditions['classroomMediaType']);
            $sceneIds['sceneIds'] = empty($sceneIds['sceneIds']) ? $sceneIdsByClassroomMediaType : array_intersect($sceneIds['sceneIds'], $sceneIdsByClassroomMediaType);
        }

        if (!empty($conditions['classroomTaskId'])) {
            $sceneIdsByClassroomTaskId = $this->findSceneIdsByClassroomTaskId($conditions['classroomTaskId']);
            $sceneIds['sceneIds'] = empty($sceneIds['sceneIds']) ? $sceneIdsByClassroomTaskId : array_intersect($sceneIds['sceneIds'], $sceneIdsByClassroomTaskId);
        }

        if (!isset($sceneIds['sceneIds'])) {
            $sceneIds = [];
        } elseif ($sceneIds['sceneIds'] == []) {
            $sceneIds = [-1];
        }

        return  $sceneIds;
    }

    public function buildConditions($poolId, $conditions)
    {
    }

    public function findSceneIdsByClassroomCourseSetId($courseSetId)
    {
        $activityTestPapers = $this->getActivityService()->findActivitiesByCourseSetIdAndType($courseSetId, 'testpaper', true);
        $activityHomeWorks = $this->getActivityService()->findActivitiesByCourseSetIdAndType($courseSetId, 'homework', true);
        $activityExercises = $this->getActivityService()->findActivitiesByCourseSetIdAndType($courseSetId, 'exercise', true);
        $activates = array_merge($activityTestPapers, $activityHomeWorks, $activityExercises);

        return $this->generateSceneIds($activates);
    }

    public function findSceneIdsByClassroomMediaType($targetId, $mediaType)
    {
        if (!in_array($mediaType, ['testpaper', 'homework', 'exercise'])) {
            return [];
        }

        $classroomCourses = $this->getClassroomService()->findByClassroomId($targetId);
        if (empty($classroomCourses)) {
            return [];
        }

        $activates = $this->getActivityService()->findActivitiesByCourseIdsAndType(ArrayToolkit::column($classroomCourses, 'courseId'), $mediaType, true);

        return $this->generateSceneIds($activates);
    }

    public function findSceneIdsByClassroomTaskId($courseTaskId)
    {
        $courseTask = $this->getCourseTaskService()->getTask($courseTaskId);
        if (empty($courseTask)) {
            return [];
        }

        return $this->findSceneIdsByClassroomCourseSetId($courseTask['fromCourseSetId']);
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
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->biz->service('Classroom:ClassroomService');
    }

    /**
     * @return TaskService
     */
    protected function getCourseTaskService()
    {
        return $this->biz->service('Task:TaskService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->biz->service('Course:CourseSetService');
    }
}
