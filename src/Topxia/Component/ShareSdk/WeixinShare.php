<?php
namespace Topxia\Component\ShareSdk;

class WeixinShare
{
    protected $config;

    protected $userAgent = 'Topxia OAuth Client 1.0';

    protected $connectTimeout = 30;

    protected $timeout = 30;

    const JSAPI_TICKET_URL = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket';
    const ACCESS_TOKEN_URL = 'https://api.weixin.qq.com/cgi-bin/token';

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function getAccessToken()
    {
        $params = array(
            'appid'      => $this->config['key'],
            'secret'     => $this->config['secret'],
            'grant_type' => 'client_credential'
        );
        $result   = $this->getRequest(self::ACCESS_TOKEN_URL, $params);
        $rawToken = array();
        $rawToken = json_decode($result, true);
        return array(
            'expires_in'   => $rawToken['expires_in'],
            'access_token' => $rawToken['access_token']
        );
    }

    public function getJsApiTicket()
    {
        $token = $this->getAccessToken();

        $params = array(
            'type'         => 'jsapi',
            'access_token' => $token['access_token']
        );
        $result   = $this->getRequest(self::JSAPI_TICKET_URL, $params);
        $rawToken = array();
        $rawToken = json_decode($result, true);
        return array(
            'ticket'     => $rawToken['ticket'],
            'expires_in' => $rawToken['expires_in']
        );
    }

    public function getRequest($url, $params)
    {
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
