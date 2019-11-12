<?php

namespace Biz\Content\Event;

use Biz\System\Service\SettingService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NavigationEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'navigation.create' => 'onNavigationOperate',
            'navigation.update' => 'onNavigationOperate',
            'navigation.delete' => 'onNavigationOperate',
        );
    }

    public function onNavigationOperate(Event $event)
    {
        $navigation = $event->getSubject();

        if ('top' == $navigation['type']) {
            $newcomerTask = $this->getSettingService()->get('newcomer_task', array());
            $newcomerTask = array_merge($newcomerTask, array('top_navigation_applied' => 1));
            $this->getSettingService()->set('newcomer_task', $newcomerTask);
        }
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }
}
