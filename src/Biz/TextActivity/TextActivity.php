<?php

namespace Biz\TextActivity;


use Biz\Activity\Config\Activity;

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
        return array(
            'text.finish' => 'Biz\\TextActivity\\Listener\\TextFinishListener'
        );
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
        parent::create($fields);
    }
}