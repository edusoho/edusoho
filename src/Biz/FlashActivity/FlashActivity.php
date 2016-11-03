<?php


namespace Biz\FlashActivity;


use Biz\Activity\Config\Activity;
use Biz\FlashActivity\Dao\FlashActivityDao;
use Topxia\Common\ArrayToolkit;


class FlashActivity extends Activity
{
    public function getMetas()
    {
        return array(
            'name' => 'Flash',
            'icon' => 'es-icon es-icon-flashclass'
        );
    }
    
    public function registerActions()
    {
        return array(
            'create' => 'WebBundle:FlashActivity:create',
            'edit'   => 'WebBundle:FlashActivity:edit',
            'show'   => 'WebBundle:FlashActivity:show'
        );
    }

    protected function registerListeners()
    {
        // TODO: Implement registerListeners() method.
    }

    public function create($fields)
    {
        $ppt = ArrayToolkit::parts($fields, array(
            'mediaId',
            'finishType',
            'finishDetail'
        ));

        $media          = $this->parseMedia($fields['media']);
        $ppt['mediaId'] = $media['id'];

        $biz                  = $this->getBiz();
        $ppt['createdUserId'] = $biz['user']['id'];
        $ppt['createdTime']   = time();

        $ppt = $this->getFlashActivityDao()->create($ppt);
        return $ppt;
    }

    public function update($targetId, $fields)
    {
        $updateFields = ArrayToolkit::parts($fields, array(
            'mediaId',
            'finishType',
            'finishDetail',
        ));

        $media                       = $this->parseMedia($fields['media']);
        $updateFields['mediaId']     = $media['id'];
        $updateFields['updatedTime'] = time();
        return $this->getFlashActivityDao()->update($targetId, $updateFields);
    }

    public function delete($targetId)
    {
        return $this->getFlashActivityDao()->delete($targetId);
    }

    public function get($targetId)
    {
        return $this->getFlashActivityDao()->get($targetId);
    }

    protected function parseMedia($media)
    {
        $media = json_decode($media, JSON_OBJECT_AS_ARRAY);
        return $media;
    }

    /**
     * @return FlashActivityDao
     */
    protected function getFlashActivityDao()
    {
        return $this->getBiz()->dao('FlashActivity:FlashActivityDao');
    }
    
}