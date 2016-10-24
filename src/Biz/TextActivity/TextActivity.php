<?php

namespace Biz\TextActivity;


use Biz\Activity\Config\Activity;

class TextActivity extends Activity
{
    public $name = '图文';

    public $icon = 'es-icon es-icon-graphicclass';

    protected function getEventMap()
    {
        return array(
            'text.finish' => 'Biz\\TextActivity\\Listener\\TextFinishListener'
        );
    }

    public function getActionMap()
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