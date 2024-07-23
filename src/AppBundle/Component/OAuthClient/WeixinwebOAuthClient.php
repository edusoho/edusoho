<?php

namespace AppBundle\Component\OAuthClient;

use Symfony\Component\HttpFoundation\Request;

class WeixinwebOAuthClient extends AbstractOAuthClient
{
    const USERINFO_URL = 'https://api.weixin.qq.com/sns/userinfo';
    const AUTHORIZE_URL = 'https://open.weixin.qq.com/connect/qrconnect?';
    const OAUTH_TOKEN_URL = 'https://api.weixin.qq.com/sns/oauth2/access_token';

    public function getAuthorizeUrl($callbackUrl, $weixinState)
    {
        $params = [];
        $params['appid'] = $this->config['key'];
        $params['response_type'] = 'code';
        $params['redirect_uri'] = $callbackUrl;
        $params['scope'] = 'snsapi_login';
        $params['state'] = $weixinState;

        return self::AUTHORIZE_URL.http_build_query($params);
    }

    /**
     * 微信校验令牌
     *
     * @param $session_credential
     *
     * @return bool
     */
    public function verifyCredential(Request $request, $sessionCredential)
    {
        $state = $request->query->get('state');
        if (empty($sessionCredential) || empty($state) || $sessionCredential != $state) {
            return false;
        }

        return true;
    }

    public function getAccessToken($code, $callbackUrl)
    {
        $params = [
            'appid' => $this->config['key'],
            'secret' => $this->config['secret'],
            'code' => $code,
            'grant_type' => 'authorization_code',
        ];
        $result = $this->getRequest(self::OAUTH_TOKEN_URL, $params);
        $rawToken = json_decode($result, true);
        $userInfo = $this->getUserInfo($rawToken);

        return [
            'userId' => $userInfo['id'],
            'expiredTime' => $rawToken['expires_in'],
            'access_token' => $rawToken['access_token'],
            'token' => $rawToken['access_token'],
            'openid' => $rawToken['openid'],
            'username' => $userInfo['name'],
        ];
    }

    public function getUserInfo($token)
    {
        $params = [
            'openid' => $token['openid'],
            'access_token' => $token['access_token'], ];
        $result = $this->getRequest(self::USERINFO_URL, $params);
        $info = json_decode($result, true);

        return $this->convertUserInfo($info);
    }

    private function convertUserInfo($infos)
    {
        $userInfo = [];
        $userInfo['id'] = $infos['unionid'];
        $userInfo['name'] = $infos['nickname'];
        $userInfo['avatar'] = $infos['headimgurl'];

        if (1 == $infos['sex']) {
            $userInfo['gender'] = 'male';
        } elseif (2 == $infos['sex']) {
            $userInfo['gender'] = 'female';
        }

        return $userInfo;
    }
}
