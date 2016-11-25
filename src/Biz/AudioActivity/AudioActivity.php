<?php
/**
 * User: Edusoho V8
 * Date: 02/11/2016
 * Time: 13:56
 */

namespace Biz\AudioActivity;


use Biz\Activity\Config\Activity;
use Biz\AudioActivity\Dao\AudioActivityDao;
use Topxia\Common\Exception\ResourceNotFoundException;
use Topxia\Service\Common\ServiceKernel;

class AudioActivity extends Activity
{

    /**
     * @inheritdoc
     */
    public function create($fields)
    {
        if (empty($fields['ext'])) {
            throw $this->createInvalidArgumentException('参数不正确');
        }
        $audioActivity = $this->getAudioActivityDao()->create($fields['ext']);
        return $audioActivity;
    }

    /**
     * @inheritdoc
     */
    public function update($targetId, $fields)
    {
        $audioActivityFields = $fields['ext'];

        $audioActivity = $this->getAudioActivityDao()->get($fields['mediaId']);
        if (empty($audioActivity)) {
            throw $this->createNotFoundException('教学活动不存在');
        }
        $audioActivity = $this->getAudioActivityDao()->update($fields['mediaId'], $audioActivityFields);
        return $audioActivity;
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
        $audioActivity         = $this->getAudioActivityDao()->get($id);
        $audioActivity['file'] = $this->getUploadFileService()->getFile($audioActivity['mediaId']);
        return $audioActivity;
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

    /**
     * @return AudioActivityDao
     */
    protected function getAudioActivityDao()
    {
        return $this->getBiz()->dao("AudioActivity:AudioActivityDao");
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return ServiceKernel::instance()->createService('File.UploadFileService');
    }
}