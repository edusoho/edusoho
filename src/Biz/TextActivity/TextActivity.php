<?php
/**
 * User: retamia
 * Date: 2016/10/19
 * Time: 11:13
 */

namespace Biz\TextActivity;


use Biz\Activity\Config\Activity;
use Biz\Activity\Listener\TextFinishListener;
use WebBundle\Controller\TextActivityController;

class TextActivity extends Activity
{
    public $name = '图文';

    public $icon = 'es-icon es-icon-graphicclass';

    protected function getRendererClass()
    {
        return __NAMESPACE__ . '\\' . 'TextActivityRenderer';
    }

    protected function getEventMap()
    {
        return array(
            'text.finish' => TextFinishListener::class
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