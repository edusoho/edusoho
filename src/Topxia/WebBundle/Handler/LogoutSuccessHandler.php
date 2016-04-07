<?php

namespace Topxia\WebBundle\Handler;

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Logout\DefaultLogoutSuccessHandler;

class LogoutSuccessHandler extends DefaultLogoutSuccessHandler
{
    public function onLogoutSuccess(Request $request)
    {
        $goto = $request->query->get('goto');

        $setting = $this->getSettingService()->get('login_bind');

        $user_agent = $request->server->get('HTTP_USER_AGENT');

        if (strpos($user_agent, 'MicroMessenger') && $setting['enabled'] && $setting['weixinmob_enabled']) {
            $goto = "homepage";
        }

        if (!$goto) {
            $goto = "login";
        }

        $this->targetUrl = $this->httpUtils->generateUri($request, $goto);

        if ($this->getAuthService()->hasPartnerAuth()) {
            $user = ServiceKernel::instance()->getCurrentUser();
            setcookie("REMEMBERME");

            if (!$user->isLogin()) {
                return parent::onLogoutSuccess($request);
            }

            $url     = $this->httpUtils->generateUri($request, 'partner_logout');
            $queries = array('userId' => $user['id'], 'goto' => $this->targetUrl);
            $url     = $url.'?'.http_build_query($queries);
            return $this->httpUtils->createRedirectResponse($request, $url);
        }

        //setcookie("U_LOGIN_TOKEN", '', -1);
        return parent::onLogoutSuccess($request);
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
