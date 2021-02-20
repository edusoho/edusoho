<?php

namespace Biz\Activity\Type;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Config\Activity;
use Biz\Activity\Dao\TrainingActivityDao;
use Biz\Activity\Service\ActivityService;
use Biz\Course\Service\CourseDraftService;

class Training extends Activity
{
    protected function registerListeners()
    {
        return array();
    }

    public function get($targetId)
    {
        // 获取绑定关系信息
        return [];
        // return $this->getTrainingActivityDao()->get($targetId);
    }

    public function find($ids, $showCloud = 1)
    {
        return [];
        // return $this->getTrainingActivityDao()->findByIds($ids);
    }

    public function copy($activity, $config = array())
    {
        return true;
        // $user = $this->getCurrentUser();
        // $text = $this->getTrainingActivityDao()->get($activity['mediaId']);
        // $newText = array(
        //     'finishType' => $text['finishType'],
        //     'finishDetail' => $text['finishDetail'],
        //     'createdUserId' => $user['id'],
        // );

        // return $this->getTrainingActivityDao()->create($newText);
    }

    public function sync($sourceActivity, $activity)
    {
        $sourceText = $this->getTrainingActivityDao()->get($sourceActivity['mediaId']);
        $text = $this->getTrainingActivityDao()->get($activity['mediaId']);
        $text['finishType'] = $sourceText['finishType'];
        $text['finishDetail'] = $sourceText['finishDetail'];

        return $this->getTrainingActivityDao()->update($text['id'], $text);
    }

    public function update($targetId, &$fields, $activity)
    {
        $text = ArrayToolkit::parts(
            $fields,
            array(
                'finishType',
                'finishDetail',
            )
        );

        $user = $this->getCurrentUser();
        $text['createdUserId'] = $user['id'];
        $this->getCourseDraftService()->deleteCourseDrafts(
            $activity['fromCourseId'],
            $activity['id'],
            $user['id']
        );

        return $this->getTrainingActivityDao()->update($targetId, $text);
    }

    public function delete($targetId)
    {
        return true;
        // return $this->getTrainingActivityDao()->delete($targetId);
    }

    public function create($fields)
    {
        $text['link_url'] = $fields['link_url'];
        $user = $this->getCurrentUser();
        $text['createdUserId'] = $user['id'];

        $this->getCourseDraftService()->deleteCourseDrafts($fields['fromCourseId'], 0, $user['id']);

        return $this->getTrainingActivityDao()->create($text);
    }

    /**
     * @return TextActivityDao
     */
    protected function getTrainingActivityDao()
    {
        return $this->getBiz()->dao('Activity:TrainingActivityDao');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }

    /**
     * @return CourseDraftService
     */
    protected function getCourseDraftService()
    {
        return $this->getBiz()->service('Course:CourseDraftService');
    }
}
