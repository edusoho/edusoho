<?php

namespace TrainingTaskPlugin\Biz\Activity\Type;

use Biz\Activity\Config\Activity;

class Training extends Activity
{
    protected function registerListeners()
    {
        return array();
    }



    public function create($fields)
    {
        $user = $this->getCurrentUser();
        $training['createdUserId'] = $user['id'];
        $training['dataSetId'] = 200;
        $training['containerId'] = 300;
        $this->getCourseDraftService()->deleteCourseDrafts($fields['fromCourseId'], 0, $user['id']);

        return $this->getTrainingActivityDao()->create($training);
    }
    
    public function update($targetId, &$fields, $activity)
    {
        var_dump($fields);die;
    }


    public function get($targetId)
    {
        // code
    }

    public function find($ids,$showCloud = 1)
    {
        return [];
        // code
    }

    public function copy($activity, $config = array())
    {
        // 计划被复制时调用
    }

    public function sync($sourceActivity, $activity)
    {
        // 计划被复制到班级时，源计划任务新增修改删除时调用
    }

    public function isFinished($activityId)
    {
        // code
    }

    public function delete($targetId)
    {
        // code
    }

    /**
     * @return TextActivityDao
     */
    protected function getTrainingActivityDao()
    {
        return $this->getBiz()->dao('TrainingTaskPlugin:Activity:TrainingTaskDao');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service('TrainingPlugin:TrainingTaskService');
    }

    /**
     * @return CourseDraftService
     */
    protected function getCourseDraftService()
    {
        return $this->getBiz()->service('Course:CourseDraftService');
    }
}