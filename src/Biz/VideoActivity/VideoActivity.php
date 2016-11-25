<?php

namespace Biz\VideoActivity;


use Biz\Activity\Config\Activity;
use Biz\VideoActivity\Dao\VideoActivityDao;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Exception\InvalidArgumentException;
use Topxia\Common\Exception\ResourceNotFoundException;
use Topxia\Service\Common\NotFoundException;
use Topxia\Service\Common\ServiceKernel;

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
            'video.doing' => 'Biz\\VideoActivity\\Listener\\VideoDoingListener',
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


    public function update($activityId, $fields)
    {
        $videoActivityFields = $fields['ext'];

        $videoActivity = $this->getVideoActivityDao()->get($fields['mediaId']);
        if (empty($videoActivity)) {
            throw $this->createNotFoundException('教学活动不存在');
        }
        $videoActivity = $this->getVideoActivityDao()->update($fields['mediaId'], $videoActivityFields);
        return $videoActivity;
    }

    public function get($id)
    {
        $videoActivity         = $this->getVideoActivityDao()->get($id);
        $videoActivity['file'] = $this->getUploadFileService()->getFile($videoActivity['mediaId']);
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
        return $this->getBiz()->dao('VideoActivity:VideoActivityDao');
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return ServiceKernel::instance()->createService('File.UploadFileService');
    }
}