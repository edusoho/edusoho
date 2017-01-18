<?php

namespace Biz\Activity\Type;

use Biz\Activity\Config\Activity;
use Biz\Activity\Dao\AudioActivityDao;
use Biz\File\Service\UploadFileService;
use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\ActivityLearnLogService;

class Audio extends Activity
{
    /**
     * @inheritdoc
     */
    public function create($fields)
    {
        if (empty($fields['ext'])) {
            throw $this->createInvalidArgumentException('参数不正确');
        }
        $audioActivity = $this->getAudioActivityDao()->create($fields['ext']);
        return $audioActivity;
    }

    public function copy($activity, $config = array())
    {
        $audio    = $this->getAudioActivityDao()->get($activity['mediaId']);
        $newAudio = array(
            'mediaId' => $audio['mediaId']
        );

        return $this->getAudioActivityDao()->create($newAudio);
    }

    /**
     * @inheritdoc
     */
    public function update($targetId, &$fields, $activity)
    {
        $audioActivityFields = $fields['ext'];

        $audioActivity = $this->getAudioActivityDao()->get($fields['mediaId']);
        if (empty($audioActivity)) {
            throw $this->createNotFoundException('教学活动不存在');
        }
        $audioActivity = $this->getAudioActivityDao()->update($fields['mediaId'], $audioActivityFields);
        return $audioActivity;
    }

    public function isFinished($activityId)
    {
        $logs = $this->getActivityLearnLogService()->findMyLearnLogsByActivityIdAndEvent($activityId, 'audio.finish');
        return !empty($logs);
    }

    /**
     * @inheritdoc
     */
    public function delete($id)
    {
        $this->getAudioActivityDao()->delete($id);
    }

    /**
     * @inheritdoc
     */
    public function get($id)
    {
        $audioActivity         = $this->getAudioActivityDao()->get($id);
        $audioActivity['file'] = $this->getUploadFileService()->getFullFile($audioActivity['mediaId']);
        return $audioActivity;
    }

    protected function registerListeners()
    {
        return array();
    }

    /**
     * @return AudioActivityDao
     */
    protected function getAudioActivityDao()
    {
        return $this->getBiz()->dao("Activity:AudioActivityDao");
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

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->getBiz()->service('File:UploadFileService');
    }
}
