<?php

namespace Biz\Activity\Type;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Config\Activity;
use Biz\Activity\Dao\VideoActivityDao;
use Biz\File\Service\UploadFileService;
use Biz\Activity\Service\ActivityService;
use Biz\CloudPlatform\Client\CloudAPIIOException;

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
        if ($video['finishType'] === 'time') {
            $result = $this->getTaskResultService()->getMyLearnedTimeByActivityId($activityId);
            $result /= 60;

            return !empty($result) && $result >= $video['finishDetail'];
        }

        if ($video['finishType'] === 'end') {
            $log = $this->getActivityLearnLogService()->getMyRecentFinishLogByActivityId($activityId);

            return !empty($log);
        }

        return false;
    }

    public function get($id)
    {
        $videoActivity = $this->getVideoActivityDao()->get($id);
        // Todo 临时容错处理
        try {
            $videoActivity['file'] = $this->getUploadFileService()->getFullFile($videoActivity['mediaId']);
        } catch (CloudAPIIOException $e) {
            return $videoActivity;
        }

        return $videoActivity;
    }

    public function find($ids)
    {
        $videoActivities = $this->getVideoActivityDao()->findByIds($ids);
        $mediaIds = ArrayToolkit::column($videoActivities, 'mediaId');
        try {
            $files = $this->getUploadFileService()->findFilesByIds(
                $mediaIds,
                $showCloud = 1
            );
        } catch (CloudAPIIOException $e) {
            $files = array();
        }

        if (empty($files)) {
            return $videoActivities;
        }
        $files = ArrayToolkit::index($files, 'id');
        array_walk(
            $videoActivities,
            function (&$videoActivity) use ($files) {
                $videoActivity['file'] = isset($files[$videoActivity['mediaId']]) ? $files[$videoActivity['mediaId']] : null;
            }
        );

        return $videoActivities;
    }

    public function delete($id)
    {
        return $this->getVideoActivityDao()->delete($id);
    }

    public function materialSupported()
    {
        return true;
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
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }
}
