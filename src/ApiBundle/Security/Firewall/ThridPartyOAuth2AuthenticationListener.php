<?php

namespace ApiBundle\Security\Firewall;

use AppBundle\Component\OAuthClient\OAuthClientFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ThridPartyOAuth2AuthenticationListener extends BaseAuthenticationListener
{
    const TOKEN_HEADER = 'X-Auth-Token';

    public function handle(Request $request)
    {
        if (null !== $this->getTokenStorage()->getToken()) {
            return;
        }

        if ($accessToken = $request->query->get('access_token')
            && $openid = $request->query->get('openid')
            && $type = $request->query->get('type')) {

            $client = $this->createOAuthClient($type);
            $user = $client->getUserInfo($this->makeFakeToken($type, $accessToken, $type));


            return;
        }
    }

    private function makeFakeToken($type, $accessToken, $openid)
    {
        switch ($type) {

        }
    }

    /**
     * @param $type
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
