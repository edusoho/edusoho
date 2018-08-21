<?php

namespace Biz\Activity\Type;

use Biz\Activity\Config\Activity;
use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Testpaper\Service\TestpaperService;

class Exercise extends Activity
{
    protected function registerListeners()
    {
        return array();
    }

    public function get($targetId)
    {
        return $this->getTestpaperService()->getTestpaperByIdAndType($targetId, 'exercise');
    }

    public function find($targetIds, $showCloud = 1)
    {
        return $this->getTestpaperService()->findTestpapersByIdsAndType($targetIds, 'exercise');
    }

    public function create($fields)
    {
        $fields = $this->filterFields($fields);

        return $this->getTestpaperService()->buildTestpaper($fields, 'exercise');
    }

    public function copy($activity, $config = array())
    {
        $newActivity = $config['newActivity'];
        $exercise = $this->get($activity['mediaId']);

        $newExercise = array(
            'title' => $exercise['name'],
            'itemCount' => $exercise['itemCount'],
            'passedCondition' => $exercise['passedCondition'],
            'fromCourseId' => $newActivity['fromCourseId'],
            'courseSetId' => $newActivity['fromCourseSetId'],
            'metas' => $exercise['metas'],
            'copyId' => $config['isCopy'] ? $exercise['id'] : 0,
        );

        $range = $exercise['metas']['range'];

        //新建任务同步时
        if (!empty($config['isSync']) && $config['isSync']) {
            $copyTask = $this->getTaskByCopyIdAndCourseId($range['lessonId'], $newExercise['fromCourseId']);
            $range['lessonId'] = empty($copyTask) ? 0 : $copyTask['id'];
            $range['courseId'] = empty($range['courseId']) ? 0 : $newActivity['fromCourseId'];
        } elseif ($config['isCopy']) {
            //先赋值给lessonId，方便后期修改
            $newExercise['lessonId'] = empty($exercise['metas']['range']['lessonId']) ? 0 : $exercise['metas']['range']['lessonId'];

            $range['courseId'] = empty($range['courseId']) ? 0 : $newActivity['fromCourseId'];
            //lessonId是taskId，先赋值老数据，后面task复制好之后再修改
            $range['lessonId'] = empty($range['lessonId']) ? 0 : $range['lessonId'];
        } else {
            $range['courseId'] = 0;
            $range['lessonId'] = 0;
        }

        $newExercise['metas']['range'] = $range;

        return $this->create($newExercise);
    }

    public function sync($sourceActivity, $activity)
    {
        $sourceExercise = $this->get($sourceActivity['mediaId']);
        $exercise = $this->get($activity['mediaId']);

        $fields = array(
            'name' => $sourceExercise['name'],
            'passedCondition' => $sourceExercise['passedCondition'],
            'itemCount' => $sourceExercise['itemCount'],
            'metas' => $sourceExercise['metas'],
        );

        $metas = $sourceExercise['metas'];

        if (!empty($metas['range']['lessonId'])) {
            $metas['range']['courseId'] = $exercise['courseId'];

            $copyTask = $this->getTaskByCopyIdAndCourseId($metas['range']['lessonId'], $exercise['courseId']);
            $metas['range']['lessonId'] = empty($copyTask) ? 0 : $copyTask['id'];
        }

        if (!empty($metas['range']['courseId'])) {
            $metas['range']['courseId'] = $exercise['courseId'];
        }

        $fields['metas'] = $metas;

        return $this->getTestpaperService()->updateTestpaper($activity['mediaId'], $fields);
    }

    public function update($targetId, &$fields, $activity)
    {
        $exercise = $this->get($targetId);

        if (!$exercise) {
            throw $this->createNotFoundException('教学活动不存在');
        }

        $filterFields = $this->filterFields($fields);

        return $this->getTestpaperService()->updateTestpaper($exercise['id'], $filterFields);
    }

    public function delete($targetId)
    {
        return $this->getTestpaperService()->deleteTestpaper($targetId, true);
    }

    public function isFinished($activityId)
    {
        $user = $this->getCurrentUser();

        $activity = $this->getActivityService()->getActivity($activityId);
        $exercise = $this->getTestpaperService()->getTestpaperByIdAndType($activity['mediaId'], 'exercise');

        $result = $this->getTestpaperService()->getUserLatelyResultByTestId($user['id'], $activity['mediaId'], $activity['fromCourseId'], $activity['id'], 'exercise');

        if (!$result) {
            return false;
        }

        if (!empty($exercise['passedCondition']) && 'submit' === $exercise['passedCondition']['type'] && in_array($result['status'], array('reviewing', 'finished'))) {
            return true;
        }

        return false;
    }

    protected function filterFields($fields)
    {
        $filterFields = ArrayToolkit::parts($fields, array(
            'title',
            'range',
            'itemCount',
            'difficulty',
            'questionTypes',
            'finishCondition',
            'passedCondition',
            'fromCourseId',
            'fromCourseSetId',
            'courseSetId',
            'courseId',
            'lessonId',
            'metas',
            'copyId',
        ));

        $filterFields['courseId'] = empty($filterFields['fromCourseId']) ? 0 : $filterFields['fromCourseId'];
        $filterFields['lessonId'] = empty($filterFields['lessonId']) ? 0 : $filterFields['lessonId'];
        $filterFields['name'] = empty($filterFields['title']) ? '' : $filterFields['title'];

        return $filterFields;
    }

    protected function getTaskByCopyIdAndCourseId($copyTaskId, $courseId)
    {
        $conditions = array(
            'courseId' => $courseId,
            'copyId' => $copyTaskId,
        );

        $copyTasks = $this->getTaskService()->searchTasks($conditions, array(), 0, 1);

        if (!empty($copyTasks)) {
            return $copyTasks[0];
        }

        return array();
    }

    /**
     * @return TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->getBiz()->service('Testpaper:TestpaperService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }

    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }
}
