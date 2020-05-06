<?php

namespace Biz\S2B2C\Sync\Component\Activity;

use Biz\Activity\Dao\AudioActivityDao;

class Audio extends Activity
{
    public function sync($activity, $config = [])
    {
        $newAudio = $this->getAudioActivityFields($activity, $config);

        return $this->getAudioActivityDao()->create($newAudio);
    }

    public function updateToLastedVersion($activity, $config = [])
    {
        $newAudio = $this->getAudioActivityFields($activity, $config);

        $existAudio = $this->getAudioActivityDao()->search(['syncId' => $newAudio['syncId']], [], 0, PHP_INT_MAX);
        if (!empty($existAudio)) {
            return $this->getAudioActivityDao()->update($existAudio[0]['id'], $newAudio);
        }

        return $this->getAudioActivityDao()->create($newAudio);
    }

    protected function getAudioActivityFields($activity, $config)
    {
        $audio = $activity[$activity['mediaType'].'Activity'];
        $newUploadFiles = $config['newUploadFiles'];

        return [
            'mediaId' => empty($newUploadFiles[$audio['mediaId']]) ? 0 : $newUploadFiles[$audio['mediaId']]['id'],
            'hasText' => $audio['hasText'],
            'syncId' => $audio['id'],
        ];
    }

    /**
     * @return AudioActivityDao
     */
    protected function getAudioActivityDao()
    {
        return $this->getBiz()->dao('Activity:AudioActivityDao');
    }
}
