<?php

namespace Biz\S2B2C\Sync\Component\Activity;

use Biz\Activity\Dao\AudioActivityDao;

class Audio extends Activity
{
    public function sync($activity, $config = array())
    {
        $newAudio = $this->getAudioActivityFields($activity, $config);

        return $this->getAudioActivityDao()->create($newAudio);
    }

    /**
     * @param $activity
     * @param array $config
     *
     * @return |null
     */
    public function updateToLastedVersion($activity, $config = array())
    {
        $newAudio = $this->getAudioActivityFields($activity, $config);

        $sync = $this->getSyncByRemoteResourceIdAndResourceType($newAudio['syncId'], 'activity_audio');
        unset($newAudio['syncId']);
        if (!empty($sync)) {
            $existAudio = $this->getAudioActivityDao()->get($sync['localResourceId']);
            if (!empty($existAudio)) {
                return $this->getAudioActivityDao()->update($existAudio[0]['id'], $newAudio);
            }
        }

        return $this->getAudioActivityDao()->create($newAudio);
    }

    protected function getAudioActivityFields($activity, $config)
    {
        $audio = $activity[$activity['mediaType'].'Activity'];
        $newUploadFiles = $config['newUploadFiles'];

        return array(
            'mediaId' => empty($newUploadFiles[$audio['mediaId']]) ? 0 : $newUploadFiles[$audio['mediaId']]['id'],
            'hasText' => $audio['hasText'],
            'syncId' => $audio['id'],
        );
    }

    /**
     * @return AudioActivityDao
     */
    protected function getAudioActivityDao()
    {
        return $this->getBiz()->dao('Activity:AudioActivityDao');
    }
}
