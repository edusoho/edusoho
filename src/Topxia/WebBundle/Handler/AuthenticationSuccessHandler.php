<?php

namespace Topxia\WebBundle\Handler;

use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Topxia\Service\Common\ServiceKernel;
use Topxia\WebBundle\Handler\AuthenticationHelper;

class AuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $userId = $token->getUser()->id;

        $forbidden =  AuthenticationHelper::checkLoginForbidden($request);
        if ($forbidden['status'] == 'error') {
            $exception = new AuthenticationException($forbidden['message']);
            throw $exception;
        } else {
            $this->getUserService()->markLoginSuccess($userId, $request->getClientIp());
        }

        $sessionId = $request->getSession()->getId();
        //$sessionId = $this->createToken($request);
        $this->getUserService()->rememberLoginSessionId($userId, $sessionId);

        if ($request->isXmlHttpRequest()) {
            $content = array(
                'success' => true
            );
            return new JsonResponse($content, 200);
        }

        if ($this->getAuthService()->hasPartnerAuth()) {
            $url = $this->httpUtils->generateUri($request, 'partner_login');
            $queries = array('goto' => $this->determineTargetUrl($request));
            $url = $url . '?' . http_build_query($queries);
            return $this->httpUtils->createRedirectResponse($request, $url);
        }

        return parent::onAuthenticationSuccess($request, $token);
    }

    private function createToken(Request $request)
    {
        $userLoginToken = $request->cookies->get('U_LOGIN_TOKEN');
        if (empty($userLoginToken)) {
            $userLoginToken = md5($request->getSession()->getId());
            setcookie('U_LOGIN_TOKEN', $userLoginToken, time()+3600*24*365);
        }
        return $userLoginToken;
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