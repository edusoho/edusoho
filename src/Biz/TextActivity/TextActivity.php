<?php

namespace Biz\TextActivity;


use Biz\Activity\Config\Activity;
use Biz\TextActivity\Dao\TextActivityDao;
use Topxia\Common\ArrayToolkit;

class TextActivity extends Activity
{
    public function getMetas()
    {
        return array(
            'name' => '图文',
            'icon' => 'es-icon es-icon-graphicclass'
        );
    }

    protected function registerListeners()
    {
        return array();
    }

    public function get($targetId)
    {
        return $this->getTextActivityDao()->get($targetId);
    }

    public function update($targetId, $fields)
    {
        $text = ArrayToolkit::parts($fields, array(
            'finishType',
            'finishDetail'
        ));

        $biz                  = $this->getBiz();
        $text['createdUserId'] = $biz['user']['id'];
        return $this->getTextActivityDao()->update($targetId, $text);
    }

    public function delete($targetId)
    {
        return $this->getTextActivityDao()->delete($targetId);
    }

    public function registerActions()
    {
        return array(
            'create' => 'WebBundle:TextActivity:create',
            'edit'   => 'WebBundle:TextActivity:edit',
            'show'   => 'WebBundle:TextActivity:show',
        );
    }

    public function create($fields)
    {
        $text = ArrayToolkit::parts($fields, array(
            'finishType',
            'finishDetail'
        ));
        $biz                  = $this->getBiz();
        $text['createdUserId'] = $biz['user']['id'];
        return $this->getTextActivityDao()->create($text);
    }

    /**
     * @return TextActivityDao
     */
    protected function getTextActivityDao()
    {
        return $this->getBiz()->dao('TextActivity:TextActivityDao');
    }

}