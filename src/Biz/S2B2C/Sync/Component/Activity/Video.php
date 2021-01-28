<?php

namespace Biz\S2B2C\Sync\Component\Activity;

use Biz\Activity\Dao\VideoActivityDao;

class Video extends Activity
{
    public function sync($activity, $config = [])
    {
        $newVideoFields = $this->getVideoActivityFields($activity, $config);

        return $this->getVideoActivityDao()->create($newVideoFields);
    }

    public function updateToLastedVersion($activity, $config = [])
    {
        $newVideoFields = $this->getVideoActivityFields($activity, $config);

        $existVideo = $this->getVideoActivityDao()->search(['syncId' => $newVideoFields['syncId']], [], 0, PHP_INT_MAX);
        if (!empty($existVideo)) {
            unset($existVideo['createdUserId']);

            return $this->getVideoActivityDao()->update($existVideo[0]['id'], $newVideoFields);
        }

        return $this->getVideoActivityDao()->create($newVideoFields);
    }

    protected function getVideoActivityFields($activity, $config)
    {
        $video = $activity[$activity['mediaType'].'Activity'];
        $newUploadFiles = $config['newUploadFiles'];

        return [
            'syncId' => $video['id'],
            'mediaSource' => $video['mediaSource'],
            'mediaId' => empty($newUploadFiles[$video['mediaId']]) ? 0 : $newUploadFiles[$video['mediaId']]['id'],
            'mediaUri' => $video['mediaUri'],
            'finishType' => $video['finishType'],
            'finishDetail' => $video['finishDetail'],
        ];
    }

    /**
     * @return VideoActivityDao
     */
    protected function getVideoActivityDao()
    {
        return $this->getBiz()->dao('Activity:VideoActivityDao');
    }
}
