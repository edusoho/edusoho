<?php

namespace ApiBundle\Security\Firewall;

use AppBundle\Component\OAuthClient\OAuthClientFactory;
use Biz\System\SettingException;
use Symfony\Component\HttpFoundation\Request;

class ThirdPartyOAuth2AuthenticationListener extends BaseAuthenticationListener
{
    public function handle(Request $request)
    {
        if (null !== $this->getTokenStorage()->getToken()) {
            return;
        }

        if (($accessToken = $request->request->get('access_token'))
            && ($openid = $request->request->get('openid'))
            && ($type = $request->request->get('type'))) {
            $client = $this->createOAuthClient($type);
            $thirdPartyUser = $client->getUserInfo($client->makeToken($type, $accessToken, $openid, $request->request->get('appid')));
            $this->getUserTokenFromAccessToken($request, $thirdPartyUser, $type);

            return;
        }
    }

    private function getUserTokenFromAccessToken(Request $request, $thirdPartyUser, $type)
    {
        $user = $this->getUserService()->getUserBindByTypeAndFromId($type, $thirdPartyUser['id']);
        if ($user) {
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
            throw SettingException::NOTFOUND_THIRD_PARTY_AUTH_CONFIG();
        }

        if (empty($settings) || !isset($settings[$type.'_enabled']) || empty($settings[$type.'_key']) || empty($settings[$type.'_secret'])) {
            throw SettingException::NOTFOUND_THIRD_PARTY_AUTH_CONFIG();
        }

        if (!$settings[$type.'_enabled']) {
            throw SettingException::FORBIDDEN_THIRD_PARTY_AUTH();
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
