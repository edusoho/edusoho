<?php

namespace Biz\Activity\Type;

use Biz\Activity\Config\Activity;
use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Dao\FlashActivityDao;
use Biz\Activity\Service\ActivityService;
use Biz\CloudPlatform\Client\CloudAPIIOException;
use Biz\Common\CommonException;

class Flash extends Activity
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
            'finishType' => 'time',
        );
        $fields = array_merge($default, $fields);

        $flash = ArrayToolkit::parts($fields, array(
            'mediaId',
            'finishType',
            'finishDetail',
        ));

        $user = $this->getCurrentUser();
        $flash['createdUserId'] = $user['id'];

        $flash = $this->getFlashActivityDao()->create($flash);

        return $flash;
    }

    public function copy($activity, $config = array())
    {
        $user = $this->getCurrentUser();
        $flash = $this->getFlashActivityDao()->get($activity['mediaId']);
        $newFlash = array(
            'mediaId' => $flash['mediaId'],
            'finishType' => $flash['finishType'],
            'finishDetail' => $flash['finishDetail'],
            'createdUserId' => $user['id'],
        );

        return $this->getFlashActivityDao()->create($newFlash);
    }

    public function sync($sourceActivity, $activity)
    {
        $sourceFlash = $this->getFlashActivityDao()->get($sourceActivity['mediaId']);
        $flash = $this->getFlashActivityDao()->get($activity['mediaId']);
        $flash['mediaId'] = $sourceFlash['mediaId'];
        $flash['finishType'] = $sourceFlash['finishType'];
        $flash['finishDetail'] = $sourceFlash['finishDetail'];

        return $this->getFlashActivityDao()->update($flash['id'], $flash);
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

        return $this->getFlashActivityDao()->update($targetId, $updateFields);
    }

    public function delete($targetId)
    {
        return $this->getFlashActivityDao()->delete($targetId);
    }

    public function get($targetId)
    {
        $flashActivity = $this->getFlashActivityDao()->get($targetId);

        if ($flashActivity) {
            $flashActivity['file'] = $this->getUploadFileService()->getFullFile($flashActivity['mediaId']);
        }

        return $flashActivity;
    }

    public function find($targetIds, $showCloud = 1)
    {
        $flashActivities = $this->getFlashActivityDao()->findByIds($targetIds);
        $mediaIds = ArrayToolkit::column($flashActivities, 'mediaId');
        try {
            $files = $this->getUploadFileService()->findFilesByIds(
                $mediaIds,
                $showCloud
            );
        } catch (CloudAPIIOException $e) {
            $files = array();
        }

        if (empty($files)) {
            return $flashActivities;
        }
        $files = ArrayToolkit::index($files, 'id');
        array_walk(
            $flashActivities,
            function (&$videoActivity) use ($files) {
                $videoActivity['file'] = isset($files[$videoActivity['mediaId']]) ? $files[$videoActivity['mediaId']] : null;
            }
        );

        return $flashActivities;
    }

    public function materialSupported()
    {
        return true;
    }

    public function findWithoutCloudFiles($targetIds)
    {
        return $this->getFlashActivityDao()->findByIds($targetIds);
    }

    /**
     * @return FlashActivityDao
     */
    protected function getFlashActivityDao()
    {
        return $this->getBiz()->dao('Activity:FlashActivityDao');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }

    protected function getUploadFileService()
    {
        return $this->getBiz()->service('File:UploadFileService');
    }
}
