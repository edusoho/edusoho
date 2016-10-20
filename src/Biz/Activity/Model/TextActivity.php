<?php
/**
 * User: retamia
 * Date: 2016/10/19
 * Time: 11:13
 */

namespace Biz\Activity\Model;


use Biz\Activity\Event\TextFinishEvent;
use Biz\Activity\Service\ActivityService;

class TextActivity extends Activity
{
    public $name = '图文';

    public function getRendererClass()
    {
        return __NAMESPACE__ . '\\' . 'TextActivityRenderer';
    }

    public function getEventMap()
    {
        return array(
            'text.start' => TextFinishEvent::class
        );
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