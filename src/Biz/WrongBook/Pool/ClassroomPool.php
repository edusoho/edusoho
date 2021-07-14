<?php

namespace Biz\WrongBook\Pool;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseSetService;
use Biz\Task\Service\TaskService;
use Biz\WrongBook\Dao\WrongQuestionBookPoolDao;
use Biz\WrongBook\Dao\WrongQuestionCollectDao;
use Biz\WrongBook\Service\WrongQuestionService;

class ClassroomPool extends AbstractPool
{
    public function getPoolTarget($report)
    {
        // TODO: Implement getPoolTarget() method.
    }

    public function prepareSceneIds($poolId, $conditions)
    {
        $pool = $this->getWrongQuestionBookPoolDao()->get($poolId);
        if (empty($pool) || 'classroom' != $pool['target_type']) {
            return [];
        }

        return $this->prepareCommonSceneIds($conditions, $pool['target_id']);
    }

    protected function prepareCommonSceneIds($conditions, $targetId)
    {
        if (empty($conditions['classroomCourseSetId']) && empty($conditions['classroomMediaType']) && empty($conditions['classroomTaskId'])) {
            return [];
        }

        $sceneIds = $this->findSceneIdsByClassroomId($targetId);

        if (!empty($conditions['classroomCourseSetId'])) {
            $sceneIdsByClassroomCourseSetId = $this->findSceneIdsByClassroomCourseSetId($conditions['classroomCourseSetId']);
            $sceneIds = empty($sceneIds) ? $sceneIdsByClassroomCourseSetId : array_intersect($sceneIds, $sceneIdsByClassroomCourseSetId);
        }

        if (!empty($conditions['classroomMediaType'])) {
            $sceneIdsByClassroomMediaType = $this->findSceneIdsByClassroomMediaType($targetId, $conditions['classroomMediaType']);
            $sceneIds = empty($sceneIds) ? $sceneIdsByClassroomMediaType : array_intersect($sceneIds, $sceneIdsByClassroomMediaType);
        }

        if (!empty($conditions['classroomTaskId'])) {
            $sceneIdsByClassroomTaskId = $this->findSceneIdsByClassroomTaskId($conditions['classroomTaskId']);
            $sceneIds = empty($sceneIds['sceneIds']) ? $sceneIdsByClassroomTaskId : array_intersect($sceneIds, $sceneIdsByClassroomTaskId);
        }

        return empty($sceneIds) ? [-1] : $sceneIds;
    }

    public function prepareSceneIdsByTargetId($targetId, $conditions)
    {
        $this->getClassroomService()->tryManageClassroom($targetId);

        $conditions = array_merge($conditions, [
            'classroomId' => $targetId,
        ]);

        return $this->prepareCommonSceneIds($conditions, $targetId);
    }

    public function buildConditions($pool, $conditions)
    {
        $searchConditions = [];
        $collects = $this->getWrongQuestionCollectDao()->findCollectBYPoolId($pool['id']);
        $wrongQuestions = $this->getWrongQuestionService()->searchWrongQuestion(['collect_ids' => ArrayToolkit::column($collects, 'id')], [], 0, PHP_INT_MAX);
        $wrongQuestions = ArrayToolkit::group($wrongQuestions, 'answer_scene_id');
        $courSets = $this->classroomCourseSetIdSearch($pool['target_id'], $wrongQuestions);
        $searchConditions['courseSets'] = $courSets;
        $searchConditions['mediaTypes'] = empty($courSets) ? [] : $this->classroomMediaTypeSearch($courSets, $conditions, $wrongQuestions);
        $searchConditions['tasks'] = empty($courSets) ? [] : $this->classroomTaskIdSearch($courSets, $conditions, $wrongQuestions);
        $classroom = $this->getClassroomService()->getClassroom($pool['target_id']);
        $searchConditions['title'] = $classroom['title'];

        return $searchConditions;
    }

    public function buildTargetConditions($targetId, $conditions)
    {
        $searchConditions = [];
        $pools = $this->getWrongQuestionService()->searchWrongBookPool(['target_type' => 'classroom', 'target_id' => $targetId], [], 0, PHP_INT_MAX);
        $poolIds = empty($pools) ? [-1] : ArrayToolkit::column($pools, 'id');
        $wrongQuestions = $this->getWrongQuestionService()->searchWrongQuestionsWithCollect(['pool_ids' => $poolIds], [], 0, PHP_INT_MAX);
        $wrongQuestions = ArrayToolkit::group($wrongQuestions, 'answer_scene_id');
        $courSets = $this->classroomCourseSetIdSearch($targetId, $wrongQuestions);
        $searchConditions['courseSets'] = $courSets;
        $searchConditions['mediaTypes'] = empty($courSets) ? [] : $this->classroomMediaTypeSearch($courSets, $conditions, $wrongQuestions);
        $searchConditions['tasks'] = empty($courSets) ? [] : $this->classroomTaskIdSearch($courSets, $conditions, $wrongQuestions);

        return $searchConditions;
    }

    protected function classroomCourseSetIdSearch($classroomId, $wrongQuestions)
    {
        if (empty($wrongQuestions)) {
            return [];
        }

        $classroomCourses = $this->getClassroomService()->findCoursesByClassroomId($classroomId);
        $courseSetIds = ArrayToolkit::column($classroomCourses, 'courseSetId');
        $courseSets = $this->getCourseSetService()->findCourseSetsByIds($courseSetIds);
        $courseSetsGroupId = ArrayToolkit::index($courseSets, 'id');
        $activates = $this->findActivatesByTestPaperAndHomeworkAndExerciseAndCourseSetIds($courseSetIds);

        $courseSetInfo = [];
        $tempCourseSet = [];
        foreach ($activates as $activity) {
            $courseSetId = $courseSetsGroupId[$activity['fromCourseSetId']]['id'];
            if (!empty($activity['ext']) && isset($wrongQuestions[$activity['ext']['answerSceneId']]) && $tempCourseSet !== $courseSetIds && !in_array($courseSetId, $tempCourseSet)) {
                $courseSetInfo[] = [
                    'id' => $courseSetsGroupId[$activity['fromCourseSetId']]['id'],
                    'title' => $courseSetsGroupId[$activity['fromCourseSetId']]['title'],
                ];
                $tempCourseSet[] = $courseSetId;
            }
        }

        return $courseSetInfo;
    }

    protected function classroomMediaTypeSearch($courSets, $conditions, $wrongQuestions)
    {
        $defaultMediaType = ['homework', 'testpaper', 'exercise'];
        $courseSetIds = ArrayToolkit::column($courSets, 'id');
        if (!empty($conditions['classroomCourseSetId'])) {
            $courseSetIds = [$conditions['classroomCourseSetId']];
        }

        $activates = $this->findActivatesByTestPaperAndHomeworkAndExerciseAndCourseSetIds($courseSetIds);
        $mediaType = [];
        foreach ($activates as $activity) {
            if (!empty($activity['ext']) && isset($wrongQuestions[$activity['ext']['answerSceneId']]) && !in_array($activity['mediaType'], $mediaType)) {
                $mediaType[] = $activity['mediaType'];
                if ($mediaType === $defaultMediaType) {
                    break;
                }
            }
        }

        return $mediaType;
    }

    protected function classroomTaskIdSearch($courSets, $conditions, $wrongQuestions)
    {
        $defaultMediaType = ['homework', 'testpaper', 'exercise'];
        $courseSetIds = ArrayToolkit::column($courSets, 'id');
        if (!empty($conditions['classroomCourseSetId'])) {
            $courseSetIds = [$conditions['classroomCourseSetId']];
        }
        if (!empty($conditions['classroomMediaType'])) {
            $mediaTypes = [$conditions['classroomMediaType']];
        } else {
            $mediaTypes = $defaultMediaType;
        }

        $activates = $this->getActivityService()->findActivitiesByCourseSetIdsAndTypes($courseSetIds, $mediaTypes, true);
        $activatesGroupById = ArrayToolkit::index($activates, 'id');
        $activityIds = ArrayToolkit::column($activates, 'id');
        $courseTasks = $this->getCourseTaskService()->findTasksByActivityIds($activityIds);

        $courseTasksInfo = [];
        foreach ($courseTasks as $courseTask) {
            $taskActivity = $activatesGroupById[$courseTask['activityId']];
            if (!empty($taskActivity['ext']) && isset($wrongQuestions[$taskActivity['ext']['answerSceneId']])) {
                $courseTasksInfo[] = [
                    'id' => $courseTask['id'],
                    'title' => $courseTask['title'],
                ];
            }
        }

        return $courseTasksInfo;
    }

    protected function findSceneIdsByClassroomId($classroomId)
    {
        $classroomCourses = $this->getClassroomService()->findCoursesByClassroomId($classroomId);
        $courseSetIds = ArrayToolkit::column($classroomCourses, 'courseSetId');

        $activates = $this->findActivatesByTestPaperAndHomeworkAndExerciseAndCourseSetIds($courseSetIds);

        return $this->generateSceneIds($activates);
    }

    protected function findSceneIdsByClassroomCourseSetId($courseSetId)
    {
        $activityTestPapers = $this->getActivityService()->findActivitiesByCourseSetIdAndType($courseSetId, 'testpaper', true);
        $activityHomeWorks = $this->getActivityService()->findActivitiesByCourseSetIdAndType($courseSetId, 'homework', true);
        $activityExercises = $this->getActivityService()->findActivitiesByCourseSetIdAndType($courseSetId, 'exercise', true);
        $activates = array_merge($activityTestPapers, $activityHomeWorks, $activityExercises);

        return $this->generateSceneIds($activates);
    }

    protected function findSceneIdsByClassroomMediaType($targetId, $mediaType)
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

    protected function findSceneIdsByClassroomTaskId($courseTaskId)
    {
        $courseTask = $this->getCourseTaskService()->getTask($courseTaskId);
        if (empty($courseTask)) {
            return [];
        }

        $activity = $this->getActivityService()->getActivity($courseTask['activityId'], true);

        return $this->generateSceneIds([$activity]);
    }

    protected function findActivatesByTestPaperAndHomeworkAndExerciseAndCourseSetIds($courseSetIds)
    {
        $activityTestPapers = $this->getActivityService()->findActivitiesByCourseSetIdsAndType($courseSetIds, 'testpaper', true);
        $activityHomeWorks = $this->getActivityService()->findActivitiesByCourseSetIdsAndType($courseSetIds, 'homework', true);
        $activityExercises = $this->getActivityService()->findActivitiesByCourseSetIdsAndType($courseSetIds, 'exercise', true);

        return array_merge($activityTestPapers, $activityHomeWorks, $activityExercises);
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
     * @return WrongQuestionCollectDao
     */
    protected function getWrongQuestionCollectDao()
    {
        return $this->biz->dao('WrongBook:WrongQuestionCollectDao');
    }

    /**
     * @return WrongQuestionService
     */
    protected function getWrongQuestionService()
    {
        return  $this->biz->service('WrongBook:WrongQuestionService');
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
