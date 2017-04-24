<?php

namespace AppBundle\Listener;

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class UserLoginIpCheckListener
{
    public function __construct($container)
    {
        $this->container = $container;
    }

    public function onUserLoginIpCheckListener(GetResponseEvent $event)
    {
        if ($event->getRequestType() != HttpKernelInterface::MASTER_REQUEST) {
            return;
        }

        $request = $event->getRequest();
        $session = $request->getSession();

        if (empty($session)) {
            return;
        }

        $user = $this->getUserService()->getCurrentUser();

        if (!$user->islogin()) {
            return;
        }

        if ($user['currentIp'] != $request->getSession()->get('loginIp')) {
            $goto = $this->container->get('router')->generate('logout');
            $response = new RedirectResponse($goto, '302');
            $event->setResponse($response);
        }
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User:UserService');
    }
}
