<?php
namespace Topxia\Component\OAuthClient;

class RenrenOAuthClient extends AbstractOAuthClient
{
    CONST AUTHORIZE_URL = 'https://graph.renren.com/oauth/authorize?';
    CONST OAUTH_TOKEN_URL = 'https://graph.renren.com/oauth/token?';

    public function getAuthorizeUrl($callbackUrl)
    {
        $params = array();
        $params['response_type'] = 'code';
        $params['client_id'] = $this->config['key'];
        $params['redirect_uri'] = $callbackUrl;
        $params['scope'] = 'publish_feed publish_share';
        return self::AUTHORIZE_URL . http_build_query($params);
    }

    public function getAccessToken($code, $callbackUrl)
    {
        $params = array();
        $params['client_id'] = $this->config['key'];
        $params['client_secret'] = $this->config['secret'];
        $params['authorization_code'] = 'code';
        $params['redirect_uri'] = $callbackUrl;
        $params['code'] = $code;
        $params['grant_type'] = 'authorization_code';
        $data = $this->postRequest(self:: OAUTH_TOKEN_URL . http_build_query($params), array());
        $token = json_decode($data, true);
        $token['userId'] = $token['user']['id'];
        $token['token'] = $token['access_token'];
        $token['expiredTime'] = $token['expires_in'];
        return $token;
    }

    public function getUserInfo($token)
    {
         return $this->convertUserInfo($token['user']);
    }

    protected function convertUserInfo($infos)
    {
        $userInfo = array();
        $userInfo['id'] = $infos['id'];
        $userInfo['name'] = $infos['name'];
        $userInfo['smallAvatar'] = $infos['avatar'][1]['url'];
        $userInfo['largeAvatar'] = $infos['avatar'][2]['url'];
        return $userInfo;
    }

    public function getClientInfo()
    {
        return array(
            'type' => 'renren',
            'name' => '人人',
        );
    }
}