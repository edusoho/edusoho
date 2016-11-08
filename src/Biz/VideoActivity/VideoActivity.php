<?php

namespace Biz\VideoActivity;


use Biz\Activity\Config\Activity;
use Biz\VideoActivity\Dao\VideoActivityDao;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Exception\InvalidArgumentException;

class VideoActivity extends Activity
{
    public function getMetas()
    {
        return array(
            'name' => '视频',
            'icon' => 'es-icon es-icon-videoclass'
        );
    }

    protected function registerListeners()
    {
        return array(
            'video.start'  => 'Biz\\VideoActivity\\Listener\\VideoStartListener',
            'video.finish' => 'Biz\\VideoActivity\\Listener\\VideoFinishListener'
        );
    }

    public function registerActions()
    {
        return array(
            'create' => 'WebBundle:VideoActivity:create',
            'edit'   => 'WebBundle:VideoActivity:edit',
            'show'   => 'WebBundle:VideoActivity:show',
        );
    }

    public function create($fields)
    {
        $videoActivity    = $this->getVideoExt($fields);
        $videoActivity = $this->getVideoActivityDao()->create($videoActivity);
        return $videoActivity;
    }


    public function update($activityId, $fields)
    {
        $videoActivity      = $this->getVideoExt($fields);
        $videoActivity = $this->getVideoActivityDao()->update($fields['mediaId'], $videoActivity);
        return $videoActivity;
    }

    public function get($id)
    {
        return $this->getVideoActivityDao()->get($id);
    }

    public function delete($id)
    {
        return $this->getVideoActivityDao()->delete($id);
    }


    protected function getVideoExt($fields)
    {
        $media = json_decode($fields['media'], true);
        return array(
            'mediaSource' => $media['source'],
            'mediaId'     => empty($media['id']) ? null : $media['id'],
            'mediaUri'    => empty($media['uri']) ? null : $media['uri'],
            'media'       => $media
        );
    }

    protected function getVideoLength($fields)
    {
        $length = 0;
        if (isset($fields['minute']) && $fields['minute'] > 0) {
            $length += $fields['minute'] * 60;
        }
        if (!isset($fields['second'])) {
            throw new InvalidArgumentException($message = 'lack of necessary fields');
        }
        $length += $fields['second'];
        return $length;
    }

    /**
     * @return VideoActivityDao
     */
    protected function getVideoActivityDao()
    {
        return $this->getBiz()->dao('VideoActivity:VideoActivityDao');
    }
}