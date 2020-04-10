<?php

namespace Biz\Activity\Type;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Config\Activity;
use Biz\Activity\Dao\PptActivityDao;
use Biz\Activity\Service\ActivityService;
use Biz\Common\CommonException;
use Biz\File\Service\UploadFileService;

class Ppt extends Activity
{
    protected function registerListeners()
    {
    }

    public function create($fields)
    {
        if (empty($fields['media'])) {
            throw CommonException::ERROR_PARAMETER();
        }
        $media = json_decode($fields['media'], true);

        if (empty($media['id'])) {
            throw CommonException::ERROR_PARAMETER();
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

        $user = $this->getCurrentUser();
        $ppt['createdUserId'] = $user['id'];
        $ppt['createdTime'] = time();

        $ppt = $this->getPptActivityDao()->create($ppt);

        return $ppt;
    }

    public function copy($activity, $config = array())
    {
        $user = $this->getCurrentUser();
        $ppt = $this->getPptActivityDao()->get($activity['mediaId']);
        $newPpt = array(
            'mediaId' => $ppt['mediaId'],
            'finishType' => $ppt['finishType'],
            'finishDetail' => $ppt['finishDetail'],
            'createdUserId' => $user['id'],
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
        if (empty($fields['media'])) {
            throw CommonException::ERROR_PARAMETER();
        }
        $media = json_decode($fields['media'], true);

        if (empty($media['id'])) {
            throw CommonException::ERROR_PARAMETER();
        }
        $fields['mediaId'] = $media['id'];

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
        $activity = $this->getPptActivityDao()->get($targetId);

        if ($activity) {
            $activity['file'] = $this->getUploadFileService()->getFullFile($activity['mediaId']);
        }

        return $activity;
    }

    public function find($targetIds, $showCloud = 1)
    {
        return $this->getPptActivityDao()->findByIds($targetIds);
    }

    public function materialSupported()
    {
        return true;
    }

    public function findWithoutCloudFiles($targetIds)
    {
        return $this->getPptActivityDao()->findByIds($targetIds);
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

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->getBiz()->service('File:UploadFileService');
    }
}
