<?php

namespace AppBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class RewardPointNotifyListener extends AbstractSecurityDisabledListener
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if ($event->getRequestType() != HttpKernelInterface::MASTER_REQUEST) {
            return;
        }
        
        $this->container->get('reward_point.response_decorator')->decorate($response = $event->getResponse());
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User:UserService');
    }

    protected function getAccountService()
    {
        return ServiceKernel::instance()->createService('RewardPoint:AccountService');
    }

    protected function getAccountFlowService()
    {
        return ServiceKernel::instance()->createService('RewardPoint:AccountFlowService');
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->createService('System:SettingService');
    }
}
