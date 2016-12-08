<?php

namespace Biz\DiscussActivity;

use Biz\Activity\Config\Activity;

class DiscussActivity extends Activity
{
    public function getMetas()
    {
        return array(
            'name' => '讨论',
            'icon' => 'es-icon es-icon-comment'
        );
    }

    protected function registerListeners()
    {
        return array(
            'discuss.finish' => 'Biz\\DiscussActivity\\Listener\\DiscussFinishListener'
        );
    }

    public function registerActions()
    {
        return array(
            'create' => 'WebBundle:DiscussActivity:create',
            'edit'   => 'WebBundle:DiscussActivity:edit',
            'show'   => 'WebBundle:DiscussActivity:show'
        );
    }

    public function create($fields)
    {
        parent::create($fields);
    }
}
