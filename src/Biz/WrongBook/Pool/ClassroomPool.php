<?php

namespace Biz\WrongBook\Pool;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseSetService;
use Biz\Task\Service\TaskService;
use Biz\WrongBook\Dao\WrongQuestionBookPoolDao;
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
        if (empty($pool)) {
            return [];
        }

       return $this->prepareCommonSceneIds($conditions, $pool);
    }

    protected function prepareCommonSceneIds($conditions ,$pool = [])
    {
        $sceneIds = [];

        if (!empty($conditions['classroomId'])) {
            $sceneIds['sceneIds'] = $this->findSceneIdsByClassroomId($conditions['classroomId']);
        }

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
        } else {
            $sceneIds = $sceneIds['sceneIds'];
        }

        return  $sceneIds;
    }

    public function prepareSceneIdsByTargetId($targetId, $conditions)
    {
        $this->getClassroomService()->tryManageClassroom($targetId);

        $conditions = array_merge($conditions, [
            'classroomId' => $targetId,
        ]);

        return $this->prepareCommonSceneIds($conditions);
    }

    public function buildConditions($pool, $conditions)
    {
        $searchConditions = [];
        $courSets = $this->classroomCourseSetIdSearch($pool['target_id']);
        $searchConditions['courseSets'] = $courSets;
        $searchConditions['mediaTypes'] = empty($courSets) ? [] : $this->classroomMediaTypeSearch($courSets, $conditions);
        $searchConditions['tasks'] = empty($courSets) ? [] : $this->classroomTaskIdSearch($courSets, $conditions);

        return $searchConditions;
    }

    protected function classroomCourseSetIdSearch($classroomId)
    {
        $classroomCourses = $this->getClassroomService()->findCoursesByClassroomId($classroomId);
        $courseSetIds = ArrayToolkit::column($classroomCourses, 'courseSetId');
        $courseSets = $this->getCourseSetService()->findCourseSetsByIds($courseSetIds);
        $courseSetsGroupId = ArrayToolkit::index($courseSets, 'id');
        $activates = $this->findActivatesByTestPaperAndHomeworkAndExerciseAndCourseSetIds($courseSetIds);
        $wrongQuestions = $this->getWrongQuestionService()->searchWrongQuestion([
            'user_id' => $this->getCurrentUser()->getId(),
            'answer_scene_ids' => $this->generateSceneIds($activates),
        ], [], 0, PHP_INT_MAX);

        if (empty($wrongQuestions)) {
            return [];
        }
        $wrongQuestionGroupSceneIds = ArrayToolkit::group($wrongQuestions, 'answer_scene_id');
        $courseSetInfo = [];
        $tempCourseSet = [];
        foreach ($activates as $activity) {
            $courseSetId = $courseSetsGroupId[$activity['fromCourseSetId']]['id'];
            if (!empty($activity['ext']) && isset($wrongQuestionGroupSceneIds[$activity['ext']['answerSceneId']]) && $tempCourseSet !== $courseSetIds && !in_array($courseSetId, $tempCourseSet)) {
                $courseSetInfo[] = [
                    'id' => $courseSetsGroupId[$activity['fromCourseSetId']]['id'],
                    'title' => $courseSetsGroupId[$activity['fromCourseSetId']]['title'],
                ];
                $tempCourseSet[] = $courseSetId;
            }
        }

        return $courseSetInfo;
    }

    protected function classroomMediaTypeSearch($courSets, $conditions)
    {
        $defaultMediaType = ['homework', 'testpaper', 'exercise'];
        $courseSetIds = ArrayToolkit::column($courSets, 'id');
        if (!empty($conditions['classroomCourseSetId'])) {
            $courseSetIds = [$conditions['classroomCourseSetId']];
        }

        $activates = $this->findActivatesByTestPaperAndHomeworkAndExerciseAndCourseSetIds($courseSetIds);
        $wrongQuestions = $this->getWrongQuestionService()->searchWrongQuestion([
            'user_id' => $this->getCurrentUser()->getId(),
            'answer_scene_ids' => $this->generateSceneIds($activates),
        ], [], 0, PHP_INT_MAX);

        $wrongQuestionGroupSceneIds = ArrayToolkit::group($wrongQuestions, 'answer_scene_id');
        $mediaType = [];
        foreach ($activates as $activity) {
            if (!empty($activity['ext']) && isset($wrongQuestionGroupSceneIds[$activity['ext']['answerSceneId']]) && !in_array($activity['mediaType'], $mediaType)) {
                $mediaType[] = $activity['mediaType'];
                if ($mediaType === $defaultMediaType) {
                    break;
                }
            }
        }

        return $mediaType;
    }

    protected function classroomTaskIdSearch($courSets, $conditions)
    {
        $defaultMediaType = ['homework', 'testpaper', 'exercise'];
        $courseSetIds = ArrayToolkit::column($courSets, 'id');
        if (!empty($conditions['classroomCourseSetId'])) {
            $courseSetIds = [$conditions['classroomCourseSetId']];
        }
        if (!empty($conditions['classroomMediaType'])) {
            $mediaTypes = $conditions['classroomMediaType'];
        } else {
            $mediaTypes = $defaultMediaType;
        }
        $activates = $this->getActivityService()->findActivitiesByCourseSetIdsAndTypes($courseSetIds, $mediaTypes, true);
        $activatesGroupById = ArrayToolkit::index($activates, 'id');
        $wrongQuestions = $this->getWrongQuestionService()->searchWrongQuestion([
            'user_id' => $this->getCurrentUser()->getId(),
            'answer_scene_ids' => $this->generateSceneIds($activates),
        ], [], 0, PHP_INT_MAX);

        $wrongQuestionGroupSceneIds = ArrayToolkit::group($wrongQuestions, 'answer_scene_id');
        $activityIds = ArrayToolkit::column($activates, 'id');
        $courseTasks = $this->getCourseTaskService()->findTasksByActivityIds($activityIds);
        $courseTasksInfo = [];
        foreach ($courseTasks as $courseTask) {
            $taskActivity = $activatesGroupById[$courseTask['activityId']];
            if (!empty($taskActivity['ext']) && isset($wrongQuestionGroupSceneIds[$taskActivity['ext']['answerSceneId']])) {
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
        $courseSetIds = ArrayToolkit::column($classroomCourses,'courseSetId');

        $activates = $this->findActivatesByTestPaperAndHomeworkAndExerciseAndCourseSetIds($courseSetIds);
        return $this->generateSceneIds($activates);
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
