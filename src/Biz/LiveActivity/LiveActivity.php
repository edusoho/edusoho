<?php

namespace Biz\LiveActivity;

use Biz\Activity\Config\Activity;

class LiveActivity extends Activity
{
    public function getMetas()
    {
        return array(
            'name' => '直播',
            'icon' => 'es-icon es-icon-graphicclass'
        );
    }

    protected function registerListeners()
    {
        return array(
            'text.finish' => 'Biz\\LiveActivity\\Listener\\LiveFinishListener'
        );
    }

    public function registerActions()
    {
        return array(
            'create' => 'WebBundle:LiveActivity:create',
            'edit'   => 'WebBundle:LiveActivity:edit',
            'show'   => 'WebBundle:LiveActivity:show'
        );
    }

    public function create($fields)
    {
        parent::create($fields);
    }
}
