<?php

namespace Topxia\WebBundle\Handler;

use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Topxia\Service\Common\ServiceKernel;

class AuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        // $this->getUserService()->markLoginInfo();

        if ($request->isXmlHttpRequest()) {
            $content = array(
                'success' => true
            );
            return new JsonResponse($content, 200);
        }

        $userId = $token->getUser()->id;
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
}