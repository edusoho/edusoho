<?php

namespace Biz\VideoActivity;


use Biz\Activity\Config\Activity;

class VideoActivity extends Activity
{
    public function getMetas()
    {
        return array(
            'name' => '视频',
            'icon' => 'es-icon es-icon-graphicclass'
        );
    }

    protected function registerListeners()
    {
        return array(
            'vidow.start'  => 'Biz\\VideoActivity\\Listener\\VideoStartListener',
            'vidow.finish' => 'Biz\\VideoActivity\\Listener\\VideoFinishListener'
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
        parent::create($fields);
    }
}