<?php

namespace Biz\WrongBook\Pool;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Task\Service\TaskService;
use Biz\WrongBook\Dao\WrongQuestionBookPoolDao;
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
        }

        return $sceneIds;
    }

    public function prepareConditions($courseSetId, $conditions)
    {
        if (empty($conditions['courseId'])) {
            $courses = $this->getCourseService()->findPublishedCoursesByCourseSetId($courseSetId);
        } else {
            $courses[] = $this->getCourseService()->getCourse($conditions['courseId']);
        }
        $courses = ArrayToolkit::index($courses, 'id');
        $courseIds = ArrayToolkit::column($courses, 'id');
        $types = [];
        $taskTitles = [];
        $coursesTitles = [];
        foreach ($courseIds as $key => $courseId) {
            $activates = $this->findActivityByCourseId($courseId, true);

            $sceneIds = $this->generateSceneIds($activates);
            $questions = $this->getWrongQuestionService()->searchWrongQuestion(['answer_scene_ids' => $sceneIds], [], 0, PHP_INT_MAX);
            $answerSceneIds = ArrayToolkit::column($questions, 'answer_scene_id');
            if (empty($questions)) {
                unset($courseIds[$key]);
                unset($courses[$courseId]);
                continue;
            }
            $coursesTitles[$key]['id'] = $courseId;
            $coursesTitles[$key]['title'] = $courses[$courseId]['title'];
            foreach ($activates as $k => $activate) {
                if (in_array($activate['ext']['answerSceneId'], $answerSceneIds)) {
                    if (!empty($conditions['courseMediaType']) && $conditions['courseMediaType'] != $activate['mediaType']) {
                        continue;
                    }
                    $task = current($this->getCourseTaskService()->findTasksByActivityIds([$activate['id']]));
                    $types[] = $activate['mediaType'];
                    $taskTitles[$k]['id'] = $task['id'];
                    $taskTitles[$k]['title'] = $task['title'];
                }
            }
        }
        $result['course'] = $coursesTitles;
        $result['mediaTypes'] = array_unique($types);
        $result['tasks'] = $taskTitles;

        return $result;
    }

    public function findSceneIdsByCourseId($courseId)
    {
        $activityTestPapers = $this->getActivityService()->findActivitiesByCourseIdAndType($courseId, 'testpaper', true);
        $activityHomeWorks = $this->getActivityService()->findActivitiesByCourseIdAndType($courseId, 'homework', true);
        $activityExercises = $this->getActivityService()->findActivitiesByCourseIdAndType($courseId, 'exercise', true);
        $activates = array_merge($activityTestPapers, $activityHomeWorks, $activityExercises);

        return $this->generateSceneIds($activates);
    }

    public function findActivityByCourseId($courseId)
    {
        $activityTestPapers = $this->getActivityService()->findActivitiesByCourseIdAndType($courseId, 'testpaper', true);
        $activityHomeWorks = $this->getActivityService()->findActivitiesByCourseIdAndType($courseId, 'homework', true);
        $activityExercises = $this->getActivityService()->findActivitiesByCourseIdAndType($courseId, 'exercise', true);
        $activates = array_merge($activityTestPapers, $activityHomeWorks, $activityExercises);

        return $activates;
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
