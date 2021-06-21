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

    public function findSceneIdsByCourseSetId($courseSetId)
    {
        $activityTestPapers = $this->getActivityService()->findActivitiesByCourseSetIdAndType($courseSetId, 'testpaper', true);
        $activityHomeWorks = $this->getActivityService()->findActivitiesByCourseSetIdAndType($courseSetId, 'homework', true);
        $activityExercises = $this->getActivityService()->findActivitiesByCourseSetIdAndType($courseSetId, 'exercise', true);
        $activates = array_merge($activityTestPapers, $activityHomeWorks, $activityExercises);

        return $this->generateSceneIds($activates);
    }

    public function findSceneIdsByClassroomMediaType($poolId, $mediaType)
    {
        if (!in_array($mediaType, ['testpaper', 'homework', 'exercise'])) {
            return [];
        }

        $pool = $this->getWrongQuestionBookPoolDao()->get($poolId);
        $classroomCourses = $this->getClassroomService()->findByClassroomId($pool['target_id']);

        if (empty($pool) || 'classroom' != $pool['target_type'] || empty($classroomCourses)) {
            return [];
        }

        $courseIds = ArrayToolkit::column($classroomCourses, 'courseId');

        $activates = $this->getActivityService()->findActivitiesByCourseIdsAndType($courseIds, $mediaType, true);

        return $this->generateSceneIds($activates);
    }

    public function findSceneIdsByCourseTaskId($courseTaskId)
    {
        $courseTask = $this->getCourseTaskService()->getTask($courseTaskId);
        if (empty($courseTask)) {
            return [];
        }

        return $this->findSceneIdsByCourseSetId($courseTask['fromCourseSetId']);
    }

    public function findSceneIdsByCourseSetName($courseSetName)
    {
        $courseSets = $this->getCourseSetService()->findCourseSetsLikeTitle($courseSetName);

        if (empty($courseSets)) {
            return [];
        }

        $courseSetIds = ArrayToolkit::column($courseSets, 'courseId');

        $activityTestPapers = $this->getActivityService()->findActivitiesByCourseSetIdsAndType($courseSetIds, 'testpaper', true);
        $activityHomeWorks = $this->getActivityService()->findActivitiesByCourseSetIdsAndType($courseSetIds, 'homework', true);
        $activityExercises = $this->getActivityService()->findActivitiesByCourseSetIdsAndType($courseSetIds, 'exercise', true);
        $activates = array_merge($activityTestPapers, $activityHomeWorks, $activityExercises);

        return $this->generateSceneIds($activates);
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
