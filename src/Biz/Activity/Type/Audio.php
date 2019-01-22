<?php

namespace Biz\Activity\Type;

use Biz\Activity\ActivityException;
use Biz\Activity\Config\Activity;
use Biz\Activity\Dao\AudioActivityDao;
use Biz\CloudPlatform\Client\CloudAPIIOException;
use Biz\Common\CommonException;
use Biz\File\Service\UploadFileService;
use AppBundle\Common\ArrayToolkit;

class Audio extends Activity
{
    /**
     * {@inheritdoc}
     */
    public function create($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('media'))) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }
        $media = json_decode($fields['media'], true);

        if (empty($media['id'])) {
            throw CommonException::ERROR_PARAMETER();
        }
        $media['mediaId'] = $media['id'];
        $audio = ArrayToolkit::parts($media, array('mediaId'));
        $audio['hasText'] = isset($fields['hasText']) ? $fields['hasText'] : 0;
        $audioActivity = $this->getAudioActivityDao()->create($audio);

        return $audioActivity;
    }

    public function copy($activity, $config = array())
    {
        $audio = $this->getAudioActivityDao()->get($activity['mediaId']);
        $newAudio = array(
            'mediaId' => $audio['mediaId'],
            'hasText' => $audio['hasText'],
        );

        return $this->getAudioActivityDao()->create($newAudio);
    }

    public function sync($sourceActivity, $activity)
    {
        $sourceAudio = $this->getAudioActivityDao()->get($sourceActivity['mediaId']);
        $audio = $this->getAudioActivityDao()->get($activity['mediaId']);
        $audio['mediaId'] = $sourceAudio['mediaId'];
        $audio['hasText'] = $sourceAudio['hasText'];

        return $this->getAudioActivityDao()->update($audio['id'], $audio);
    }

    /**
     * {@inheritdoc}
     */
    public function update($targetId, &$fields, $activity)
    {
        if (!ArrayToolkit::requireds($fields, array('media', 'hasText'))) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }
        $media = json_decode($fields['media'], true);

        if (empty($media['id'])) {
            throw CommonException::ERROR_PARAMETER();
        }

        $audioActivityFields = array(
            'mediaId' => $media['id'],
            'hasText' => $fields['hasText'],
        );
        $audioActivity = $this->getAudioActivityDao()->get($activity['mediaId']);
        if (empty($audioActivity)) {
            throw ActivityException::NOTFOUND_ACTIVITY();
        }
        $audioActivity = $this->getAudioActivityDao()->update($activity['mediaId'], $audioActivityFields);

        return $audioActivity;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        $this->getAudioActivityDao()->delete($id);
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        $audioActivity = $this->getAudioActivityDao()->get($id);
        $audioActivity['file'] = $this->getUploadFileService()->getFullFile($audioActivity['mediaId']);

        return $audioActivity;
    }

    public function find($targetIds, $showCloud = 1)
    {
        $audioActivities = $this->getAudioActivityDao()->findByIds($targetIds);
        $mediaIds = ArrayToolkit::column($audioActivities, 'mediaId');
        $groupMediaIds = array_chunk($mediaIds, 50);
        $files = array();
        try {
            foreach ($groupMediaIds as $mediaIds) {
                $chuckFiles = $this->getUploadFileService()->findFilesByIds($mediaIds, $showCloud);
                $files = array_merge($files, $chuckFiles);
            }
        } catch (CloudAPIIOException $e) {
            $files = array();
        }
        if (empty($files)) {
            return $audioActivities;
        }
        $files = ArrayToolkit::index($files, 'id');
        array_walk(
            $audioActivities,
            function (&$videoActivity) use ($files) {
                $videoActivity['file'] = isset($files[$videoActivity['mediaId']]) ? $files[$videoActivity['mediaId']] : null;
            }
        );

        return $audioActivities;
    }

    public function findWithoutCloudFiles($targetIds)
    {
        return $this->getAudioActivityDao()->findByIds($targetIds);
    }

    public function materialSupported()
    {
        return true;
    }

    protected function registerListeners()
    {
        return array('watching' => 'Biz\Activity\Listener\VideoActivityWatchListener');
    }

    /**
     * @return AudioActivityDao
     */
    protected function getAudioActivityDao()
    {
        return $this->getBiz()->dao('Activity:AudioActivityDao');
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->getBiz()->service('File:UploadFileService');
    }
}
