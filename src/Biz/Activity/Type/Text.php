<?php

namespace Biz\Activity\Type;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Config\Activity;
use Biz\Activity\Dao\TextActivityDao;
use Biz\Activity\Service\ActivityService;
use Biz\Course\Service\CourseDraftService;

class Text extends Activity
{
    protected function registerListeners()
    {
        return array();
    }

    public function get($targetId)
    {
        return $this->getTextActivityDao()->get($targetId);
    }

    public function find($ids)
    {
        return $this->getTextActivityDao()->findByIds($ids);
    }

    public function copy($activity, $config = array())
    {
        $user = $this->getCurrentUser();
        $text = $this->getTextActivityDao()->get($activity['mediaId']);
        $newText = array(
            'finishType' => $text['finishType'],
            'finishDetail' => $text['finishDetail'],
            'createdUserId' => $user['id'],
        );

        return $this->getTextActivityDao()->create($newText);
    }

    public function sync($sourceActivity, $activity)
    {
        $sourceText = $this->getTextActivityDao()->get($sourceActivity['mediaId']);
        $text = $this->getTextActivityDao()->get($activity['mediaId']);
        $text['finishType'] = $sourceText['finishType'];
        $text['finishDetail'] = $sourceText['finishDetail'];

        return $this->getTextActivityDao()->update($text['id'], $text);
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

        return $this->getTextActivityDao()->update($targetId, $text);
    }

    public function isFinished($activityId)
    {
        $result = $this->getTaskResultService()->getMyLearnedTimeByActivityId($activityId);
        $result /= 60;

        $activity = $this->getActivityService()->getActivity($activityId);
        $textActivity = $this->getTextActivityDao()->get($activity['mediaId']);

        return empty($textActivity['finishDetail']) || (!empty($result) && $result >= $textActivity['finishDetail']);
    }

    public function delete($targetId)
    {
        return $this->getTextActivityDao()->delete($targetId);
    }

    public function create($fields)
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

        $this->getCourseDraftService()->deleteCourseDrafts($fields['fromCourseId'], 0, $user['id']);

        return $this->getTextActivityDao()->create($text);
    }

    /**
     * @return TextActivityDao
     */
    protected function getTextActivityDao()
    {
        return $this->getBiz()->dao('Activity:TextActivityDao');
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
