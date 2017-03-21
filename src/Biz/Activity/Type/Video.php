<?php

namespace Biz\Activity\Type;

use Biz\Activity\Config\Activity;
use Biz\Activity\Dao\VideoActivityDao;
use Biz\File\Service\UploadFileService;
use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\ActivityLearnLogService;

class Video extends Activity
{
    protected function registerListeners()
    {
        return array('watching' => 'Biz\Activity\Listener\VideoActivityWatchListener');
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

    public function copy($activity, $config = array())
    {
        $video = $this->getVideoActivityDao()->get($activity['mediaId']);
        $newVideo = array(
            'mediaSource' => $video['mediaSource'],
            'mediaId' => $video['mediaId'],
            'mediaUri' => $video['mediaUri'],
            'finishType' => $video['finishType'],
            'finishDetail' => $video['finishDetail'],
        );

        return $this->getVideoActivityDao()->create($newVideo);
    }

    public function sync($sourceActivity, $activity)
    {
        $sourceVideo = $this->getVideoActivityDao()->get($sourceActivity['mediaId']);
        $video = $this->getVideoActivityDao()->get($activity['mediaId']);
        $video['mediaSource'] = $sourceVideo['mediaSource'];
        $video['mediaId'] = $sourceVideo['mediaId'];
        $video['mediaUri'] = $sourceVideo['mediaUri'];
        $video['finishType'] = $sourceVideo['finishType'];
        $video['finishDetail'] = $sourceVideo['finishDetail'];

        return $this->getVideoActivityDao()->update($video['id'], $video);
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
            throw new \Exception('教学活动不存在');
        }
        $videoActivity = $this->getVideoActivityDao()->update($fields['mediaId'], $video);

        return $videoActivity;
    }

    public function isFinished($activityId)
    {
        $activity = $this->getActivityService()->getActivity($activityId);
        $video = $this->getVideoActivityDao()->get($activity['mediaId']);
        if ($video['finishType'] == 'time') {
            $result = $this->getActivityLearnLogService()->sumMyLearnedTimeByActivityId($activityId);
            $result /= 60;

            return !empty($result) && $result >= $video['finishDetail'];
        }

        if ($video['finishType'] == 'end') {
            $logs = $this->getActivityLearnLogService()->findMyLearnLogsByActivityIdAndEvent($activityId, 'video.finish');

            return !empty($logs);
        }

        return false;
    }

    public function get($id)
    {
        $videoActivity = $this->getVideoActivityDao()->get($id);
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

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->getBiz()->service('File:UploadFileService');
    }

    /**
     * @return ActivityLearnLogService
     */
    protected function getActivityLearnLogService()
    {
        return $this->getBiz()->service('Activity:ActivityLearnLogService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }
}
