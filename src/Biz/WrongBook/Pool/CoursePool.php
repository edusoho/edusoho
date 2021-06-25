<?php

namespace Biz\WrongBook\Pool;

use Biz\Activity\Service\ActivityService;
use Biz\Task\Service\TaskService;
use Biz\User\CurrentUser;
use Biz\WrongBook\Dao\WrongQuestionBookPoolDao;
use AppBundle\Common\ArrayToolkit;
use Codeages\Biz\Framework\Context\Biz;

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

    public function prepareConditions($courseId, $conditions)
    {
        $courses=$this->getCourseService()->findPublishedCoursesByCourseSetId($courseId);
        $conditions['courseIds']=ArrayToolkit::column($courses,'id');
        $conditions=$this->handleConditions($conditions);
        $tasks=$this->getCourseTaskService()->searchTasks($conditions,[],0,PHP_INT_MAX);
        $coursesTitles=ArrayToolkit::columns($courses,['id','title']);
        $result['plans']=array_combine($coursesTitles[0],$coursesTitles[1]);
        $result['source']=array_unique(ArrayToolkit::column($tasks,'type'));
        $taskTitles=ArrayToolkit::columns($tasks,['id','title']);
        $result['tasks']=array_combine($taskTitles[0],$taskTitles[1]);
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

    protected function handleConditions($conditions)
    {
        if(empty($conditions['courseId'])){
            unset($conditions['courseId']);
        }else{
            unset($conditions['courseIds']);
        }
        if(!empty($conditions['courseMediaType'])){
            $conditions['type']=$conditions['courseMediaType'];
            unset($conditions['courseMediaType']);
        }else{
            $conditions['types']=['testpaper','exercise','homework'];
        }
        if(!empty($conditions['courseTaskId'])){
            $conditions['id']=$conditions['courseTaskId'];
            unset($conditions['courseTaskId']);
        }
        return $conditions;
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
    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }
}
