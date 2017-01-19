<?php

namespace Biz\Activity\Type;

use Biz\Activity\Dao\PptActivityDao;
use Biz\Activity\Service\ActivityLearnLogService;
use Biz\Activity\Service\ActivityService;
use Topxia\Common\ArrayToolkit;
use Biz\Activity\Config\Activity;

class Ppt extends Activity
{
    public function registerActions()
    {
        return array(
            'edit'   => 'AppBundle:Ppt:edit',
            'show'   => 'AppBundle:Ppt:show',
            'create' => 'AppBundle:Ppt:create'
        );
    }

    protected function registerListeners()
    {
    }

    public function getMetas()
    {
        return array(
            'name' => 'PPT',
            'icon' => 'es-icon es-icon-pptclass'
        );
    }

    public function isFinished($activityId)
    {
        $activity = $this->getActivityService()->getActivity($activityId);
        $ppt      = $this->getPptActivityDao()->get($activity['mediaId']);

        if ($ppt['finishType'] == 'time') {
            $result = $this->getActivityLearnLogService()->sumMyLearnedTimeByActivityId($activityId);
            return !empty($result) && $result >= $ppt['finishDetail'];
        }

        if($ppt['finishType'] == 'end'){
            $logs = $this->getActivityLearnLogService()->findMyLearnLogsByActivityIdAndEvent($activityId, 'ppt.finish');
            return !empty($logs);
        }

        return false;
    }

    public function create($fields)
    {
        $ppt = ArrayToolkit::parts($fields, array(
            'mediaId',
            'finishType',
            'finishDetail'
        ));

        $biz                  = $this->getBiz();
        $ppt['createdUserId'] = $biz['user']['id'];
        $ppt['createdTime']   = time();

        $ppt = $this->getPptActivityDao()->create($ppt);
        return $ppt;
    }

    public function update($targetId, &$fields, $activity)
    {
        $updateFields = ArrayToolkit::parts($fields, array(
            'mediaId',
            'finishType',
            'finishDetail'
        ));

        $updateFields['updatedTime'] = time();
        return $this->getPptActivityDao()->update($targetId, $updateFields);
    }

    public function delete($targetId)
    {
        return $this->getPptActivityDao()->delete($targetId);
    }

    public function get($targetId)
    {
        return $this->getPptActivityDao()->get($targetId);
    }

    /**
     * @return PptActivityDao
     */
    protected function getPptActivityDao()
    {
        return $this->getBiz()->dao('Activity:PptActivityDao');
    }

    /**
     * @return ActivityLearnLogService
     */
    protected function getActivityLearnLogService()
    {
        return $this->getBiz()->service("Activity:ActivityLearnLogService");
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service("Activity:ActivityService");
    }
}
