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
        $flash = ArrayToolkit::parts($fields, array(
            'mediaId',
            'finishType',
            'finishDetail'
        ));

        $biz                  = $this->getBiz();
        $flash['createdUserId'] = $biz['user']['id'];

        $flash = $this->getFlashActivityDao()->create($flash);
        return $flash;
    }

    public function update($targetId, $fields)
    {
        $updateFields = ArrayToolkit::parts($fields, array(
            'mediaId',
            'finishType',
            'finishDetail',
        ));

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

    /**
     * @return FlashActivityDao
     */
    protected function getFlashActivityDao()
    {
        return $this->getBiz()->dao('FlashActivity:FlashActivityDao');
    }
    
}