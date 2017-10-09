<?php

namespace ApiBundle\EventListener;

use ApiBundle\Security\Firewall\XAuthTokenAuthenticationListener;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;
use Topxia\Service\Common\ServiceKernel;

class AuthenticateListener
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onAuthenticate(AuthenticationEvent $event)
    {
        $token = $event->getAuthenticationToken();
        $request = $this->container->get('request');
        $authToken = $request->headers->get(XAuthTokenAuthenticationListener::TOKEN_HEADER);
        if (!empty($authToken)) {
            $this->onlineSample($request, $token);
        }
    }

    protected function onlineSample($request, $token)
    {
        $user = $token->getUser();
        $online = array(
            'sess_id' => $request->headers->get(XAuthTokenAuthenticationListener::TOKEN_HEADER),
            'user_id' => $user['id'],
            'ip' => $request->getClientIp(),
            'user_agent' => $request->headers->get('User-Agent', ''),
            'source' => 'App',
        );
        $this->getOnlineService()->saveOnline($online);
    }

    private function getOnlineService()
    {
        return ServiceKernel::instance()->createService('Session:OnlineService');
    }

}
