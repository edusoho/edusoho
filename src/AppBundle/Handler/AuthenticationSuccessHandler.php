<?php

namespace AppBundle\Handler;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Topxia\Service\Common\ServiceKernel;

class AuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $forbidden = AuthenticationHelper::checkLoginForbidden($request);

        if ('error' === $forbidden['status']) {
            throw new AuthenticationException($forbidden['message']);
        }

        if ($request->isXmlHttpRequest()) {
            $content = [
                'success' => true,
            ];

            return new JsonResponse($content, 200);
        }

        $currentUser = $this->getServiceKernel()->getCurrentUser();
        if(!$currentUser->isAccountNonLocked()) {
            throw new AuthenticationException("账号已被禁用");
        }

        if (!$currentUser['passwordInit']) {
            $url = $this->httpUtils->generateUri($request, 'password_init');
            $queries = ['goto' => $this->determineTargetUrl($request)];
            $url = $url.'?'.http_build_query($queries);

            return $this->httpUtils->createRedirectResponse($request, $url);
        }

        if ($this->getAuthService()->hasPartnerAuth()) {
            $url = $this->httpUtils->generateUri($request, 'partner_login');
            $queries = ['goto' => $this->determineTargetUrl($request)];
            $url = $url.'?'.http_build_query($queries);

            return $this->httpUtils->createRedirectResponse($request, $url);
        }

        return parent::onAuthenticationSuccess($request, $token);
    }

    private function getAuthService()
    {
        return ServiceKernel::instance()->createService('User:AuthService');
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->createService('System:SettingService');
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
