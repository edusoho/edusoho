<?php

namespace ApiBundle\Security\Firewall;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;

class NewTokenAuthenticationListener extends BaseAuthenticationListener
{
    const NEW_TOKEN_HEADER = 'Access-Token';

    public function handle(Request $request)
    {
        if (null === $tokenInHeader = $request->headers->get(self::NEW_TOKEN_HEADER)) {
            return;
        }
        $token = $this->getTokenStorage()->getToken();

        if (null !== $token && !$token instanceof AnonymousToken) {
            return;
        }
        $setting = $this->getSettingService()->get('api');

        if (empty($setting['external_switch'])) {
            throw new AccessDeniedHttpException('API设置未开启');
        }
        if (md5($setting['api_app_id'].'-'.$setting['api_app_secret_key']) !== $tokenInHeader) {
            throw new AccessDeniedHttpException('应用授权信息错误');
        }

        //校验ip白名单
        if (empty($setting['ip_white_list']) || !in_array($request->getClientIp(), $setting['ip_white_list'])) {
            throw new AccessDeniedHttpException('应用IP未加入白名单');
        }

        $rawToken = $this->getUserService()->findUserByType('system');
        $rawToken = array_shift($rawToken);
        $token = $this->createTokenFromRequest($request, $rawToken['id']);
        $this->getTokenStorage()->setToken($token);
    }

    protected function getSettingService()
    {
        return $this->container->get('biz')->service('System:SettingService');
    }
}
