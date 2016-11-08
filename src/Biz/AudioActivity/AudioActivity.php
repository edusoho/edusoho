<?php
/**
 * User: Edusoho V8
 * Date: 02/11/2016
 * Time: 13:56
 */

namespace Biz\AudioActivity;


use Biz\Activity\Config\Activity;
use Biz\AudioActivity\Dao\AudioActivityDao;

class AudioActivity extends Activity
{

    /**
     * @inheritdoc
     */
    public function create($fields)
    {
        $videoActivity = $this->getAudioExt($fields);
        $videoActivity = $this->getAudioActivityDao()->create($videoActivity);
        return $videoActivity;
    }

    /**
     * @inheritdoc
     */
    public function update($targetId, $fields)
    {
        $videoActivity      = $this->getAudioExt($fields);
        $videoActivity      = $this->getAudioActivityDao()->update($fields['mediaId'], $videoActivity);
        return $videoActivity;
    }

    /**
     * @inheritdoc
     */
    public function delete($id)
    {
        $this->getAudioActivityDao()->delete($id);
    }

    /**
     * @inheritdoc
     */
    public function get($id)
    {
        return $this->getAudioActivityDao()->get($id);
    }

    public function registerActions()
    {
        return array(
            'create' => 'WebBundle:AudioActivity:create',
            'edit'   => 'WebBundle:AudioActivity:edit',
            'show'   => 'WebBundle:AudioActivity:show',
        );
    }

    protected function registerListeners()
    {
        return array(
            'audio.start'  => 'Biz\\AudioActivity\\Listener\\AudioStartListener',
            'audio.finish' => 'Biz\\AudioActivity\\Listener\\AudioFinishListener'
        );
    }

    public function getMetas()
    {
        return array(
            'name' => '音频',
            'icon' => 'es-icon es-icon-audioclass'
        );
    }

    protected function getAudioExt($fields)
    {
        $media = json_decode($fields['media'], true);
        return array(
            'mediaId' => $media['id'],
            'media'   => $media
        );
    }

    /**
     * @return AudioActivityDao
     */
    protected function getAudioActivityDao()
    {
        return $this->getBiz()->dao("AudioActivity:AudioActivityDao");
    }
}