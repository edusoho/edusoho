<?php

namespace Biz\Activity\Type;


use Biz\Activity\Config\Activity;
use Biz\Activity\Dao\VideoActivityDao;
use Topxia\Service\Common\ServiceKernel;

class Video extends Activity
{
    protected function getListeners()
    {
        return array(
            'video.start'  => 'Biz\\VideoActivity\\Listener\\VideoStartListener',
            'video.doing'  => 'Biz\\VideoActivity\\Listener\\VideoWatchingListener',
            'video.finish' => 'Biz\\VideoActivity\\Listener\\VideoFinishListener'
        );
    }

    public function create($fields)
    {
        if (empty($fields['ext'])) {
            throw $this->createInvalidArgumentException('参数不正确');
        }

        $videoActivity = $fields['ext'];
        if (empty($videoActivity['mediaId'])) {
            $videoActivity['mediaId'] = 0;
        }
        $videoActivity = $this->getVideoActivityDao()->create($videoActivity);
        return $videoActivity;
    }


    public function update($activityId, $fields)
    {
        $videoActivityFields = $fields['ext'];

        $videoActivity = $this->getVideoActivityDao()->get($fields['mediaId']);
        if (empty($videoActivity)) {
            throw $this->createNotFoundException('教学活动不存在');
        }
        $videoActivity = $this->getVideoActivityDao()->update($fields['mediaId'], $videoActivityFields);
        return $videoActivity;
    }

    /**
     * TODO观看后完成
     */
    public function isFinished($activityId)
    {
        $result = $this->getActivityLearnLogService()->sumLearnedTimeByActivityId($activityId);
        $activity = $this->getActivityService()->getActivity($activityId);
        return !empty($result) 
                && $result > $activity['length'];
    }

    public function get($id)
    {
        $videoActivity         = $this->getVideoActivityDao()->get($id);
        $videoActivity['file'] = $this->getUploadFileService()->getFile($videoActivity['mediaId']);
        return $videoActivity;
    }

    public function delete($id)
    {
        return $this->getVideoActivityDao()->delete($id);
    }

    /**
     * @return VideoActivityDao
     */
    protected function getVideoActivityDao()
    {
        return $this->getBiz()->dao('Activity:VideoActivityDao');
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return ServiceKernel::instance()->createService('File.UploadFileService');
    }
}