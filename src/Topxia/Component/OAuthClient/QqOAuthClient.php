<?php
namespace Topxia\Component\OAuthClient;

class QqOAuthClient extends AbstractOAuthClient
{   
    CONST USERINFO_URL = 'https://graph.qq.com/user/get_user_info';
    CONST AUTHORIZE_URL = 'https://graph.qq.com/oauth2.0/authorize?';
    CONST OAUTH_TOKEN_URL = 'https://graph.qq.com/oauth2.0/token';
    CONST OAUTH_ME_URL = 'https://graph.qq.com/oauth2.0/me';

    public function getAuthorizeUrl($callbackUrl)
    {
        $params = array();
        $params['client_id'] = $this->config['key'];
        $params['response_type'] = 'code';
        $params['redirect_uri'] = $callbackUrl;
        $params['status'] = 'pro';
        return self::AUTHORIZE_URL . http_build_query($params);
    }

    public function getAccessToken($code, $callbackUrl)
    {
        $params = array(
            'grant_type' => 'authorization_code', 
            'client_id' => $this->config['key'], 
            'redirect_uri' => $callbackUrl, 
            'client_secret' => $this->config['secret'], 
            'code' => $code
        );
        $result = $this->getRequest(self::OAUTH_TOKEN_URL, $params);
        $rawToken = array();
        parse_str($result, $rawToken); 
        $userInfo = $this->getUserInfo($rawToken);
        return  array(
            'userId' => $userInfo['id'],
            'expiredTime' => $rawToken['expires_in'],
            'access_token' => $rawToken['access_token'],
            'token' => $rawToken['access_token']
        );
    }

    public function getUserInfo($token)
    {   
        $params = array('access_token' => $token['access_token']);
        $result = $this->getRequest(self::OAUTH_ME_URL, $params);
        if (strpos($result, "callback") !== false) {
            $lpos = strpos($result, "(");
            $rpos = strrpos($result, ")");
            $result = substr($result, $lpos + 1, $rpos - $lpos - 1);
        }
        $user = json_decode($result);
        $token['id'] = $user->openid;
        $params = array(
            'oauth_consumer_key' => $this->config['key'] ,
            'openid' => $token['id'] , 
            'format' => 'json',
            'access_token' => $token['access_token']);
        $result = $this->getRequest(self::USERINFO_URL, $params);
        $info = json_decode($result, true);
        $info['id'] = $token['id'];
        return $this->convertUserInfo($info);
    }

    private function convertUserInfo ($infos) {
        $userInfo = array();
        $userInfo['id'] = $infos['id'];
        $userInfo['name'] = $infos['nickname'];
        $userInfo['smallAvatar'] = empty($infos['figureurl_1']) ? '' : $infos['figureurl_1'];
        $userInfo['largeAvatar'] = empty($infos['figureurl_2']) ? '' : $infos['figureurl_2'];
        if ($infos['gender'] == '男') {
            $infos['gender'] = 'male';
        } elseif ($infos['gender'] == '女') {
            $infos['gender'] = 'female';
        } else {
            $infos['gender'] = 'secret';
        }
        return $userInfo;
    }

    public function getClientInfo()
    {
        return array('type' => 'qq','name' => 'QQ');
    }
}