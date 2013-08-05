<?php

namespace Topxia\WebBundle\Security;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\RememberMe\TokenBasedRememberMeServices;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;


class LoginManager 
{
    private $securityContext;
    private $userChecker;
    private $sessionStrategy;
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    final public function loginUser($firewallName, UserInterface $user, Response $response = null)
    {
        $token = $this->createToken($firewallName, $user);

        $config = $this->container->getParameter('remember_me');
        $config['always_remember_me'] = true;

        if (null !== $response) {
            $rememberMeServices = new TokenBasedRememberMeServices(
                array($this->container->get('topxia.user_provider')),
                $config['key'],
                $firewallName,
                $config,
                $this->container->get('monolog.logger.security'));

            $rememberMeServices->loginSuccess($this->container->get('request'), $response, $token);
        }

        $this->container->get('security.context')->setToken($token);

        $this->container->get('event_dispatcher')->dispatch(
            SecurityEvents::INTERACTIVE_LOGIN,
            new InteractiveLoginEvent($this->container->get('request'), $token));

    }

    protected function createToken($firewall, UserInterface $user)
    {
        return new UsernamePasswordToken($user, null, $firewall, $user->getRoles());
    }
}