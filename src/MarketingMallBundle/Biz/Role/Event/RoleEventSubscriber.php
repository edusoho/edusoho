<?php

namespace MarketingMallBundle\Biz\Role\Event;

use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Event\EventSubscriber;
use MarketingMallBundle\Biz\Role\Service\RoleService;

class RoleEventSubscriber extends EventSubscriber
{
    public static function getSubscribedEvents()
    {
        return [
            'marketing_mall.init' => 'onMarketingMallInit',
        ];
    }

    public function onMarketingMallInit(Event $event)
    {
        $this->getRoleService()->refreshMarketingMallAdminRole();
    }

    /**
     * @return RoleService
     */
    protected function getRoleService()
    {
        return $this->getBiz()->service('Role:RoleService');
    }
}
