<?php
/**
 * This file is part of the edusoho.
 * User: Ilham Tahir <yantaq@bilig.biz>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace ApiBundle\Security\Firewall;

use Symfony\Component\HttpFoundation\Request;
use AppBundle\Component\OAuthClient\OAuthClientFactory;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class H5ThirdPartyOAuth2AuthenticationListener extends BaseAuthenticationListener
{
    public function handle(Request $request)
    {
        if (null !== $this->getTokenStorage()->getToken()) {
            return;
        }

        if (($code = $request->query->get('code'))
            && ($type = $request->query->get('type'))) {
            $client = $this->createOAuthClient($type);
            $accessToken = $client->getAccessToken($code, '');
            $thirdPartyUser = $client->getUserInfo($accessToken);
            $this->getUserTokenFromAccessToken($request, $thirdPartyUser, $type);

            return;
        }
    }

    private function getUserTokenFromAccessToken(Request $request, $thirdPartyUser, $type)
    {
        $user = $this->getUserService()->getUserBindByTypeAndFromId($type, $thirdPartyUser['id']);
        if ($user) {
            $this->checkUserLocked($user['toId']);
            $token = $this->createTokenFromRequest($request, $user['toId']);
            $this->getTokenStorage()->setToken($token);
        }

        return null;
    }

    /**
     * @param $type
     *
     * @return \AppBundle\Component\OAuthClient\AbstractOauthClient
     */
    protected function createOAuthClient($type)
    {
        $settings = $this->getSettingService()->get('login_bind');

        if (empty($settings)) {
            throw new AccessDeniedHttpException('第三方登录系统参数尚未配置，请先配置。');
        }

        if (empty($settings) || !isset($settings[$type.'_enabled']) || empty($settings[$type.'_key']) || empty($settings[$type.'_secret'])) {
            throw new AccessDeniedHttpException(sprintf('第三方登录(%s)系统参数尚未配置，请先配置。', $type));
        }

        if (!$settings[$type.'_enabled']) {
            throw new AccessDeniedHttpException(sprintf('第三方登录(%s)未开启', $type));
        }

        $config = array('key' => $settings[$type.'_key'], 'secret' => $settings[$type.'_secret']);

        $client = OAuthClientFactory::create($type, $config);

        return $client;
    }

    /**
     * @return \Biz\System\Service\SettingService
     */
    private function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    private function createService($service)
    {
        return $this->container->get('biz')->service($service);
    }
}
