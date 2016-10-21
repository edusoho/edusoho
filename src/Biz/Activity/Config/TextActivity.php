<?php
/**
 * User: retamia
 * Date: 2016/10/19
 * Time: 11:13
 */

namespace Biz\Activity\Config;


use Biz\Activity\Service\ActivityService;

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
            'text.start' => TextFinishListener::class
        );
    }

    public function create($fields)
    {
        parent::create($fields);
    }


}

class TextActivityRenderer extends ActivityRenderer
{
    public function renderCreating()
    {
        return $this->render('WebBundle:ActivityManage:text.html.twig', array(
            'currentType' => 'text'
        ));
    }

    public function renderEditing($activityId)
    {
        $activity = $this->getActivityService()->getActivity($activityId);
        return $this->render('WebBundle:ActivityManage:text.html.twig', array(
            'currentType' => 'text',
            'activity'    => $activity
        ));
    }

    public function renderShow($activityId)
    {
        // TODO: Implement renderShow() method.
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }
}