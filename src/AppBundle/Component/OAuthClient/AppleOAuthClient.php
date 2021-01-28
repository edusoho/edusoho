<?php

namespace AppBundle\Component\OAuthClient;

use AppBundle\Common\Exception\UnexpectedValueException;
use Biz\System\Service\SettingService;
use Firebase\JWT\JWT;
use Topxia\Service\Common\ServiceKernel;

class AppleOAuthClient extends AbstractOAuthClient
{
    const AUTHORIZE_URL = 'https://appleid.apple.com/auth/authorize?';
    const OAUTH_TOKEN_URL = 'https://appleid.apple.com/auth/token';

    public function getAuthorizeUrl($callbackUrl)
    {
        $params = [];
        $params['client_id'] = $this->config['clientId'];
        $params['redirect_uri'] = $callbackUrl;
        $params['response_type'] = 'code';
        $params['scope'] = 'scope';
        $params['state'] = 'es-state';

        return self::AUTHORIZE_URL.http_build_query($params);
    }

    public function getAccessToken($code, $callbackUrl)
    {
        if (empty($this->config['clientId'])) {
            $rawToken = $this->getTokenFromCloud($code);
        } else {
            $data = $this->getTokenFromApple($code);
            $rawToken = json_decode($data, true);
        }

        $this->checkError($rawToken);

        return $rawToken;
    }

    public function getUserInfo($token)
    {
        $code = $token['access_token'];
        $userId = $token['openid'];
        $data = $this->getAccessToken($code, '');
        $idTokenClaim = $this->parseToken($data['id_token']);
        if ($userId != $idTokenClaim['sub']) {
            throw new UnexpectedValueException('unAuthorize');
        }

        return [
            'id' => $userId,
            'name' => '',
            'avatar' => '',
        ];
    }

    protected function getTokenFromCloud($code)
    {
        $biz = ServiceKernel::instance()->getBiz();

        return $biz['ESCloudSdk.mobile']->getAppleToken($code);
    }

    protected function getTokenFromApple($code)
    {
        $params = [
            'client_id' => $this->config['clientId'],
            'client_secret' => $this->getClientSecret(),
            'code' => $code,
            'grant_type' => 'authorization_code',
        ];

        return $this->postRequest(self::OAUTH_TOKEN_URL, http_build_query($params));
    }

    protected function parseToken($token)
    {
        $claim = explode('.', $token)[1];

        return json_decode(base64_decode($claim), true);
    }

    protected function getClientSecret()
    {
        $appleSetting = $this->getSettingService()->get('apple', []);
        if (!empty($appleSetting['clientSecret']) && $appleSetting['expireTime'] > time()) {
            return $appleSetting['clientSecret'];
        }

        $currentTime = time();
        $expireTime = $currentTime + 86400 * 30;
        $claim = [
            'iss' => $this->config['teamId'],
            'iat' => $currentTime,
            'exp' => $expireTime,
            'aud' => 'https://appleid.apple.com',
            'sub' => $this->config['clientId'],
        ];
        $clientSecret = JWT::encode($claim, $this->config['secret'], 'ES256', $this->config['key']);
        $this->getSettingService()->set('apple', ['clientSecret' => $clientSecret, 'expireTime' => $expireTime]);

        return $clientSecret;
    }

    protected function checkError($data)
    {
        if (!empty($data['error'])) {
            throw new UnexpectedValueException('unAuthorize');
        }

        return;
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return ServiceKernel::instance()->createService('System:SettingService');
    }
}
