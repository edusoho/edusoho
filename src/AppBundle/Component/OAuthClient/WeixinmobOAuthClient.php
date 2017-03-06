<?php

namespace AppBundle\Component\OAuthClient;

class WeixinmobOAuthClient extends AbstractOAuthClient
{
    const USERINFO_URL = 'https://api.weixin.qq.com/sns/userinfo';
    const AUTHORIZE_URL = 'https://open.weixin.qq.com/connect/oauth2/authorize?';
    const OAUTH_TOKEN_URL = 'https://api.weixin.qq.com/sns/oauth2/access_token';

    public function getAuthorizeUrl($callbackUrl)
    {
        $params = array();
        $params['appid'] = $this->config['key'];
        $params['redirect_uri'] = $callbackUrl;
        $params['response_type'] = 'code';
        $params['scope'] = 'snsapi_userinfo';

        return self::AUTHORIZE_URL.http_build_query($params).'#wechat_redirect';
    }

    public function getAccessToken($code, $callbackUrl)
    {
        $params = array(
            'appid' => $this->config['key'],
            'secret' => $this->config['secret'],
            'code' => $code,
            'grant_type' => 'authorization_code',
        );
        $result = $this->getRequest(self::OAUTH_TOKEN_URL, $params);
        $rawToken = array();
        $rawToken = json_decode($result, true);
        $userInfo = $this->getUserInfo($rawToken);

        return array(
            'userId' => $userInfo['id'],
            'expiredTime' => $rawToken['expires_in'],
            'access_token' => $rawToken['access_token'],
            'token' => $rawToken['access_token'],
            'openid' => $rawToken['openid'],
        );
    }

    public function getUserInfo($token)
    {
        $params = array('access_token' => $token['access_token']);
        $params = array(
            'openid' => $token['openid'],
            'access_token' => $token['access_token'],
            'lang' => 'zh_CN', );
        $result = $this->getRequest(self::USERINFO_URL, $params);
        $info = json_decode($result, true);

        return $this->convertUserInfo($info);
    }

    private function convertUserInfo($infos)
    {
        $userInfo = array();
        $userInfo['id'] = $infos['unionid'];
        $userInfo['name'] = $infos['nickname'];
        $userInfo['avatar'] = $infos['headimgurl'];

        if ($infos['sex'] == 1) {
            $userInfo['gender'] = 'male';
        } elseif ($infos['sex'] == 2) {
            $userInfo['gender'] = 'female';
        }

        return $userInfo;
    }
}
