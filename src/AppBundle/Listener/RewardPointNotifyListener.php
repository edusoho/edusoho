<?php

namespace AppBundle\Listener;

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class RewardPointNotifyListener extends AbstractSecurityDisabledListener
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if ($event->getRequestType() != HttpKernelInterface::MASTER_REQUEST) {
            return;
        }

        $rewardPoint = $this->getSettingService()->get('reward_point', array());
        if (empty($rewardPoint)) {
            return;
        }

        $request = $event->getRequest();
        $response = $event->getResponse();
        $currentUser = $this->getUserService()->getCurrentUser();

        if ($rewardPoint['enable'] && isset($currentUser['Reward-Point-Notify'])) {
            $type = $request->headers->get('Reward-Point-Notify-Type');

            if ($type == 'no-refresh') {
                $response->headers->set('Reward-Point-Notify', json_encode($currentUser['Reward-Point-Notify']));
            } else {
                $request->getSession()->set('Reward-Point-Notify', json_encode($currentUser['Reward-Point-Notify']));
            }
        }
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
