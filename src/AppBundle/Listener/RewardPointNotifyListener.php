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

        $request = $event->getRequest();
        $response = $event->getResponse();
        // file_put_contents('lis.txt', 'list'.date('H-m-s', time()));
        $currentUser = $this->getUserService()->getCurrentUser();
        // file_put_contents('currentUser.txt', serialize($currentUser).date('H-m-s', time()));

        if (isset($currentUser['Reward-Point-Notify'])) {
            // if ($currentUser['Reward-Point-Notify']['way'] == 'elite_thread') {
                // file_put_contents('on.txt', serialize($currentUser).date('H-m-s', time()));
            // }

            if ($currentUser['Reward-Point-Notify']['way'] == 'reply_discussion' || $currentUser['Reward-Point-Notify']['way'] == 'reply_question' || $currentUser['Reward-Point-Notify']['way'] == 'elite_thread') {
                // $response->headers->set('Reward-Point-Notify', json_encode($currentUser['Reward-Point-Notify']));
                header('Reward-Point-Notify:'.json_encode($currentUser['Reward-Point-Notify']));
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
}
