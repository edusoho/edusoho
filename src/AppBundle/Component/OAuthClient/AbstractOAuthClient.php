<?php

namespace AppBundle\Component\OAuthClient;

abstract class AbstractOAuthClient
{
    protected $config;

    protected $userAgent = 'Topxia OAuth Client 1.0';

    protected $connectTimeout = 30;

    protected $timeout = 30;

    protected $request;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function makeToken($type, $accessToken, $openid, $appid = '')
    {
        switch ($type) {
            case 'weibo':
                $token = [
                    'userId' => $openid,
                    'token' => $accessToken,
                ];
                break;
            case 'qq':
                $token = [
                    'openid' => $openid,
                    'access_token' => $accessToken,
                    'key' => $appid,
                ];
                break;
            case 'weixinmob':
            case 'weixinweb':
            case 'apple':
                $token = [
                    'openid' => $openid,
                    'access_token' => $accessToken,
                ];
                break;
            default:
                throw new \InvalidArgumentException('Bad type');
        }

        return $token;
    }

    abstract public function getAuthorizeUrl($callbackUrl);

    abstract public function getAccessToken($code, $callbackUrl);

    abstract public function getUserInfo($token);

    /**
     * HTTP POST.
     *
     * @param string $url    要请求的url地址
     * @param array  $params 请求的参数
     *
     * @return string
     */
    public function postRequest($url, $params)
    {
        if (isset($this->request)) {
            return $this->request->postRequest($url, $params);
        }

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        curl_setopt($curl, CURLOPT_URL, $url);

        // curl_setopt($curl, CURLINFO_HEADER_OUT, TRUE );

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }

    public function getRequest($url, $params)
    {
        if (isset($this->request)) {
            return $this->request->getRequest($url, $params);
        }

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);

        $url = $url.'?'.http_build_query($params);
        curl_setopt($curl, CURLOPT_URL, $url);

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }
}
