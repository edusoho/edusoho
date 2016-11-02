<?php
/**
 * User: Edusoho V8
 * Date: 02/11/2016
 * Time: 13:56
 */

namespace Biz\AudioActivity;


use Biz\Activity\Config\Activity;

class AudioActivity extends   Activity
{
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


}