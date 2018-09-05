<?php

require_once 'dao/flash_activity_dao.php';

use Biz\Activity\Config\Activity;
use flash\dao\flash_activity_dao;
use AppBundle\Common\ArrayToolkit;
use Biz\CloudPlatform\Client\CloudAPIIOException;

class activity_flash extends Activity
{
    protected function registerListeners()
    {
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
            'finishType' => 'time',
        );
        $fields = array_merge($default, $fields);

        $flash = ArrayToolkit::parts($fields, array(
            'mediaId',
            'finishType',
            'finishDetail',
        ));

        $biz = $this->getBiz();
        $flash['createdUserId'] = $biz['user']['id'];

        $flash = $this->getFlashActivityDao()->create($flash);

        return $flash;
    }

    public function copy($activity, $config = array())
    {
        $biz = $this->getBiz();
        $flash = $this->getFlashActivityDao()->get($activity['mediaId']);
        $newFlash = array(
            'mediaId' => $flash['mediaId'],
            'finishType' => $flash['finishType'],
            'finishDetail' => $flash['finishDetail'],
            'createdUserId' => $biz['user']['id'],
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
            throw $this->createInvalidArgumentException('参数不正确');
        }
        $media = json_decode($fields['media'], true);

        if (empty($media['id'])) {
            throw $this->createInvalidArgumentException('参数不正确');
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
        $flashActivity['file'] = $this->getUploadFileService()->getFullFile($flashActivity['mediaId']);

        return $flashActivity;
    }

    public function find($targetIds, $showCloud = 1)
    {
        $flashActivities = $this->getFlashActivityDao()->findByIds($targetIds);
        $mediaIds = ArrayToolkit::column($flashActivities, 'mediaId');
        try {
            $files = $this->getUploadFileService()->findFilesByIds(
                $mediaIds,
                $showCloud = 1
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

    /**
     * @return mixed
     */
    protected function getFlashActivityDao()
    {
        return $this->createDao(new flash_activity_dao($this->getBiz()));
    }

    /**
     * @return \Biz\Activity\Service\ActivityService
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
