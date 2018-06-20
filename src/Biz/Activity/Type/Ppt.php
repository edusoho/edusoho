<?php

namespace Biz\Activity\Type;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Config\Activity;
use Biz\Activity\Dao\PptActivityDao;
use Biz\Activity\Service\ActivityService;

class Ppt extends Activity
{
    protected function registerListeners()
    {
    }

    public function isFinished($activityId)
    {
        $activity = $this->getActivityService()->getActivity($activityId);
        $ppt = $this->getPptActivityDao()->get($activity['mediaId']);

        if ('time' === $ppt['finishType']) {
            $result = $this->getTaskResultService()->getMyLearnedTimeByActivityId($activityId);
            $result /= 60;

            return !empty($result) && $result >= $ppt['finishDetail'];
        }

        if ('end' === $ppt['finishType']) {
            $log = $this->getActivityLearnLogService()->getMyRecentFinishLogByActivityId($activityId);

            return !empty($log);
        }

        return false;
    }

    public function create($fields)
    {
        if (empty($fields['media'])) {
            throw $this->createInvalidArgumentException('参数不正确');
        }
        $media = json_decode($fields['media'], true);

        if (empty($media['id'])) {
            throw $this->createInvalidArgumentException('参数不正确');
        }
        $fields['mediaId'] = $media['id'];

        $default = array(
            'finishDetail' => 1,
            'finishType' => 'end',
        );
        $fields = array_merge($default, $fields);

        $ppt = ArrayToolkit::parts($fields, array(
            'mediaId',
            'finishType',
            'finishDetail',
        ));

        $biz = $this->getBiz();
        $ppt['createdUserId'] = $biz['user']['id'];
        $ppt['createdTime'] = time();

        $ppt = $this->getPptActivityDao()->create($ppt);

        return $ppt;
    }

    public function copy($activity, $config = array())
    {
        $biz = $this->getBiz();
        $ppt = $this->getPptActivityDao()->get($activity['mediaId']);
        $newPpt = array(
            'mediaId' => $ppt['mediaId'],
            'finishType' => $ppt['finishType'],
            'finishDetail' => $ppt['finishDetail'],
            'createdUserId' => $biz['user']['id'],
        );

        return $this->getPptActivityDao()->create($newPpt);
    }

    public function sync($sourceActivity, $activity)
    {
        $sourcePpt = $this->getPptActivityDao()->get($sourceActivity['mediaId']);
        $ppt = $this->getPptActivityDao()->get($activity['mediaId']);
        $ppt['mediaId'] = $sourcePpt['mediaId'];
        $ppt['finishType'] = $sourcePpt['finishType'];
        $ppt['finishDetail'] = $sourcePpt['finishDetail'];

        return $this->getPptActivityDao()->update($ppt['id'], $ppt);
    }

    public function update($targetId, &$fields, $activity)
    {
        $updateFields = ArrayToolkit::parts($fields, array(
            'mediaId',
            'finishType',
            'finishDetail',
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

    public function find($targetIds)
    {
        return $this->getPptActivityDao()->findByIds($targetIds);
    }

    public function findWithoutCloudFiles($targetIds)
    {
        return $this->getPptActivityDao()->findByIds($targetIds);
    }

    public function materialSupported()
    {
        return true;
    }

    /**
     * @return PptActivityDao
     */
    protected function getPptActivityDao()
    {
        return $this->getBiz()->dao('Activity:PptActivityDao');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }
}
