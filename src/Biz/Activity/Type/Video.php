<?php

namespace Biz\Activity\Type;

use Biz\Activity\Config\Activity;
use Biz\Activity\Dao\VideoActivityDao;

class Video extends Activity
{
    protected function registerListeners()
    {
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

    public function update($activityId, &$fields, $activity)
    {
        $video = $fields['ext'];

        if ($video['finishType'] == 'time') {
            if (empty($video['finishDetail'])) {
                throw $this->createAccessDeniedException('finish time can not be emtpy');
            }
        }
        $videoActivity = $this->getVideoActivityDao()->get($fields['mediaId']);
        if (empty($videoActivity)) {
            throw $this->createNotFoundException('教学活动不存在');
        }
        $videoActivity = $this->getVideoActivityDao()->update($fields['mediaId'], $video);
        return $videoActivity;
    }


    public function isFinished($activityId)
    {
        $activity = $this->getActivityService()->getActivity($activityId);
        $video      = $this->getVideoActivityDao()->get($activity['mediaId']);

        if ($video['finishType'] == 'time') {
            $result = $this->getActivityLearnLogService()->sumLearnedTimeByActivityId($activityId);
            return !empty($result) && $result >= $video['finishDetail'];
        }

        if($video['finishType'] == 'end'){
            $logs = $this->getActivityLearnLogService()->findMyLearnLogsByActivityIdAndEvent($activityId, 'video.finish');
            return !empty($logs);
        }

        return false;

    }

    public function get($id)
    {
        $videoActivity         = $this->getVideoActivityDao()->get($id);
        $videoActivity['file'] = $this->getUploadFileService()->getFullFile($videoActivity['mediaId']);
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

    protected function getUploadFileService()
    {
        return $this->getBiz()->service('File:UploadFileService');
    }

    protected function getActivityLearnLogService()
    {
        return $this->getBiz()->service("Activity:ActivityLearnLogService");
    }

    protected function getActivityService()
    {
        return $this->getBiz()->service("Activity:ActivityService");
    }
}
