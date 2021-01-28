<?php

namespace ApiBundle\EventListener;

use ApiBundle\Security\Firewall\XAuthTokenAuthenticationListener;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;

class AuthenticateListener
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onAuthenticate(AuthenticationEvent $event)
    {
        $request = $this->container->get('request_stack')->getMasterRequest();
        $authToken = $request->headers->get(XAuthTokenAuthenticationListener::TOKEN_HEADER);
        if (!empty($authToken)) {
            $this->container->get('user.online_track')->track($request->headers->get(XAuthTokenAuthenticationListener::TOKEN_HEADER));
        }
    }
}
