<?php

namespace Biz\Activity\Type;

use Biz\Activity\Config\Activity;
use Biz\Activity\Dao\AudioActivityDao;
use Biz\CloudPlatform\Client\CloudAPIIOException;
use Biz\File\Service\UploadFileService;
use AppBundle\Common\ArrayToolkit;

class Audio extends Activity
{
    /**
     * {@inheritdoc}
     */
    public function create($fields)
    {
        if (empty($fields['media'])) {
            throw $this->createInvalidArgumentException('参数不正确');
        }
        $media = json_decode($fields['media'], true);

        if (empty($media['id'])) {
            throw $this->createInvalidArgumentException('参数不正确');
        }
        $media['mediaId'] = $media['id'];
        $audio = ArrayToolkit::parts($media, array('mediaId'));
        $audioActivity = $this->getAudioActivityDao()->create($audio);

        return $audioActivity;
    }

    public function copy($activity, $config = array())
    {
        $audio = $this->getAudioActivityDao()->get($activity['mediaId']);
        $newAudio = array(
            'mediaId' => $audio['mediaId'],
        );

        return $this->getAudioActivityDao()->create($newAudio);
    }

    public function sync($sourceActivity, $activity)
    {
        $sourceAudio = $this->getAudioActivityDao()->get($sourceActivity['mediaId']);
        $audio = $this->getAudioActivityDao()->get($activity['mediaId']);
        $audio['mediaId'] = $sourceAudio['mediaId'];

        return $this->getAudioActivityDao()->update($audio['id'], $audio);
    }

    /**
     * {@inheritdoc}
     */
    public function update($targetId, &$fields, $activity)
    {
        if (empty($fields['media'])) {
            throw $this->createInvalidArgumentException('参数不正确');
        }
        $media = json_decode($fields['media'], true);

        if (empty($media['id'])) {
            throw $this->createInvalidArgumentException('参数不正确');
        }

        $audioActivityFields = array(
            'mediaId' => $media['id'],
        );
        $audioActivity = $this->getAudioActivityDao()->get($fields['mediaId']);
        if (empty($audioActivity)) {
            throw $this->createNotFoundException('教学活动不存在');
        }
        $audioActivity = $this->getAudioActivityDao()->update($fields['mediaId'], $audioActivityFields);

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
