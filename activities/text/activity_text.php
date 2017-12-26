<?php


require_once 'dao/text_activity_dao.php';

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Course\Service\CourseDraftService;
use text\dao\text_activity_dao;


class activity_text extends Biz\Activity\Config\Activity
{
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
        $biz = $this->getBiz();
        $text = $this->getTextActivityDao()->get($activity['mediaId']);
        $newText = array(
            'finishType' => $text['finishType'],
            'finishDetail' => $text['finishDetail'],
            'createdUserId' => $biz['user']['id'],
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

        $biz = $this->getBiz();
        $text['createdUserId'] = $biz['user']['id'];
        $this->getCourseDraftService()->deleteCourseDrafts(
            $activity['fromCourseId'],
            $activity['id'],
            $biz['user']['id']
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
        $biz = $this->getBiz();
        $text['createdUserId'] = $biz['user']['id'];

        $this->getCourseDraftService()->deleteCourseDrafts($fields['fromCourseId'], 0, $biz['user']['id']);

        return $this->getTextActivityDao()->create($text);
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

    /**
     * @return text_activity_dao
     */
    protected function getTextActivityDao()
    {
        return $this->createDao(new text_activity_dao($this->getBiz()));
    }
}