<?php

namespace Topxia\WebBundle\Listener;

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
        $request = $event->getRequest();
        $session = $request->getSession();

        if (empty($session)) {
            return;
        }

        $user = $this->getUserService()->getCurrentUser();

        if (!$user->islogin()) {
            return;
        }

        if ($event->getRequestType() == HttpKernelInterface::MASTER_REQUEST) {
            if ($user['currentIp'] != $user['loginIp']) {
                $goto     = $this->container->get('router')->generate('logout');
                $response = new RedirectResponse($goto, '302');
                $event->setResponse($response);
            }
        }
    }

    private function makeHash($user)
    {
        $string = $user['id'].$user['email'].$this->container->getParameter('secret');
        return md5($string);
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User.UserService');
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->createService('System.SettingService');
    }

    protected function getAuthService()
    {
        return ServiceKernel::instance()->createService('User.AuthService');
    }
}
