<?php

namespace Topxia\WebBundle\Handler;

use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Security\Core\Exception\LockedException;

class AuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        // $this->getUserService()->markLoginInfo();

        $userId = $token->getUser()->id;

        $loginConnect = $this->getSettingService()->get('login_bind', array());
        $default = array(
            'temporary_lock_enabled' => 0,
            'temporary_lock_allowed_times' => 5,
            'temporary_lock_minutes' => 20,
        );

        $loginConnect = array_merge($default, $loginConnect);

        if ($loginConnect['temporary_lock_enabled'] == 1){

            $user = $this->getUserService()->getUser($userId);
            if ($this->getUserService()->isUserTemporaryLockedOrLocked($user)){
                $ex = new LockedException('User account is temporay locked, you can reopen your account by resetting your password.');  
                $ex->setUser($token->getUser());  
                throw $ex;
            }

            $this->getUserService()->clearUserConsecutivePasswordErrorTimesAndLockDeadline($userId);
        }

        if ($request->isXmlHttpRequest()) {
            $content = array(
                'success' => true
            );
            return new JsonResponse($content, 200);
        }

        $sessionId = $request->getSession()->getId();

        $this->getUserService()->rememberLoginSessionId($userId, $sessionId);

        if ($this->getAuthService()->hasPartnerAuth()) {
            $url = $this->httpUtils->generateUri($request, 'partner_login');
            $queries = array('goto' => $this->determineTargetUrl($request));
            $url = $url . '?' . http_build_query($queries);
            return $this->httpUtils->createRedirectResponse($request, $url);
        }

        return parent::onAuthenticationSuccess($request, $token);
    }

    private function getUserService()
    {
        return ServiceKernel::instance()->createService('User.UserService');
    }

    private function getAuthService()
    {
        return ServiceKernel::instance()->createService('User.AuthService');
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->createService('System.SettingService');
    }
}