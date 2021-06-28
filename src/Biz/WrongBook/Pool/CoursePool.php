<?php

namespace Biz\WrongBook\Pool;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Task\Service\TaskService;
use Biz\WrongBook\Dao\WrongQuestionBookPoolDao;
use Biz\WrongBook\Dao\WrongQuestionCollectDao;
use Biz\WrongBook\Service\WrongQuestionService;

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

        return $this->prepareCommonSceneIds($conditions, $pool);
    }

    public function prepareCourseSceneIds($courseId, $conditions)
    {
        $conditions = array_merge($conditions, [
            'courseId' => $courseId,
        ]);

        return $this->prepareCommonSceneIds($conditions);
    }

    public function prepareCommonSceneIds($conditions, $pool = [])
    {
        $sceneIds = [];
        if (!empty($conditions['courseId'])) {
            $sceneIds['sceneIds'] = $this->findSceneIdsByCourseId($conditions['courseId']);
        }

        if (!empty($conditions['courseMediaType'])) {
            $sceneIdsByCourseMediaType = $this->findSceneIdsByCourseMediaType($pool['target_id'], $conditions['courseMediaType']);
            $sceneIds['sceneIds'] = empty($sceneIds['sceneIds']) ? $sceneIdsByCourseMediaType : array_intersect($sceneIds['sceneIds'], $sceneIdsByCourseMediaType);
        }

        if (!empty($conditions['courseTaskId'])) {
            $sceneIdsByCourseTaskId = $this->findSceneIdsByCourseTaskId($conditions['courseTaskId']);
            $sceneIds['sceneIds'] = empty($sceneIds['sceneIds']) ? $sceneIdsByCourseTaskId : array_intersect($sceneIds['sceneIds'], $sceneIdsByCourseTaskId);
        }

        if (!isset($sceneIds['sceneIds'])) {
            $sceneIds = [];
        } elseif ($sceneIds['sceneIds'] == []) {
            $sceneIds = [-1];
        } else {
            $sceneIds = $sceneIds['sceneIds'];
        }

        return $sceneIds;
    }

    public function buildConditions($pool, $conditions)
    {
        $courses = $this->getCourseService()->findPublishedCoursesByCourseSetId($pool['target_id']);
        $conditions['courseIds'] = ArrayToolkit::column($courses, 'id');
        $conditions = $this->handleConditions($conditions);
        $tasks = $this->getCourseTaskService()->searchTasks($conditions, [], 0, PHP_INT_MAX);

        $collects = $this->getWrongQuestionCollectDao()->getCollectBYPoolId($pool['id']);
        $collectIds = array_unique(ArrayToolkit::column($collects, 'id'));
        $wrongQuestions = $this->getWrongQuestionService()->searchWrongQuestion(['collect_ids' => $collectIds], [], 0, PHP_INT_MAX);
        $answerSceneIds = array_unique(ArrayToolkit::column($wrongQuestions, 'answer_scene_id'));

        $activitys = [];
        foreach ($answerSceneIds as $answerSceneId) {
            $activitys[] = $this->getActivityService()->getActivityByAnswerSceneId($answerSceneId);
        }
        $coursesIds = array_unique(ArrayToolkit::column($activitys, 'fromCourseId'));
        $courses = ArrayToolkit::index($courses, 'id');
        $tasks = ArrayToolkit::index($tasks, 'activityId');
        $activityIds = ArrayToolkit::column($activitys, 'id');
        $newCourses = [];
        foreach ($coursesIds as $coursesId) {
            if (!empty($courses[$coursesId])) {
                $newCourses[] = $courses[$coursesId];
            }
        }
        $newTasks = [];
        foreach ($activityIds as $activityId) {
            if (!empty($tasks[$activityId])) {
                $newTasks[] = $tasks[$activityId];
            }
        }
        $result['plans'] = $this->handleArray($newCourses, ['id', 'title']);
        $result['source'] = array_unique(ArrayToolkit::column($newTasks, 'type'));
        $result['tasks'] = $this->handleArray($newTasks, ['id', 'title']);

        return $result;
    }

    protected function handleArray($data, $fields)
    {
        $newData = [];
        foreach ($data as $key => $value) {
            foreach ($fields as $k => $field) {
                $newData[$key][$field] = $value[$field];
            }
        }

        return $newData;
    }

    protected function handleConditions($conditions)
    {
        if (empty($conditions['courseId'])) {
            unset($conditions['courseId']);
        } else {
            unset($conditions['courseIds']);
        }
        if (!empty($conditions['courseMediaType'])) {
            $conditions['type'] = $conditions['courseMediaType'];
            unset($conditions['courseMediaType']);
        } else {
            $conditions['types'] = ['testpaper', 'exercise', 'homework'];
        }
        if (!empty($conditions['courseTaskId'])) {
            $conditions['id'] = $conditions['courseTaskId'];
            unset($conditions['courseTaskId']);
        }

        return $conditions;
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
        return $this->biz->service('WrongBook:WrongQuestionService');
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

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }
}
