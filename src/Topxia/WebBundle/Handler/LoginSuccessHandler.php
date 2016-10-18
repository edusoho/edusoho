<?php

namespace Topxia\WebBundle\Handler;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Topxia\Service\Common\ServiceKernel;
use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * Custom login listener.
 */
class LoginSuccessHandler
{
    /**
     * @var AuthorizationChecker
     */
    private $checker;

    /**
     * Constructor
     *
     * @param AuthorizationChecker $checker
     * @param Doctrine             $doctrine
     */
    public function __construct(AuthorizationChecker $checker)
    {
        $this->checker = $checker;
    }

    /**
     * Do the magic.
     *
     * @param InteractiveLoginEvent $event
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {

        if ($this->checker->isGranted('IS_AUTHENTICATED_FULLY')) {
            // user has just logged in
        }

        if ($this->checker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            // user has logged in using remember_me cookie
        }

        // do some other magic here
        $user = $event->getAuthenticationToken()->getUser();

        // ...
        $this->getUserService()->markLoginInfo();

        $request   = $event->getRequest();
        $sessionId = $request->getSession()->getId();
        $request->getSession()->set('loginIp', $request->getClientIp());

        $this->getUserService()->rememberLoginSessionId($user['id'], $sessionId);
        $this->getUserService()->markLoginSuccess($user['id'], $request->getClientIp());
    }

    private function getUserService()
    {
        return ServiceKernel::instance()->createService('User.UserService');
    }
}
