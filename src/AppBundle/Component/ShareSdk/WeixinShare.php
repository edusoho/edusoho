<?php

namespace AppBundle\Component\ShareSdk;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Topxia\Service\Common\ServiceKernel;

class WeixinShare
{
    protected $config;

    /**
     * @var Logger
     */
    protected $logger = null;

    protected $userAgent = 'Topxia OAuth Client 1.0';

    protected $connectTimeout = 30;

    protected $timeout = 30;

    /**
     * 仅给单元测试mock用。
     */
    protected $mockedRequest = null;

    const JSAPI_TICKET_URL = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket';
    const ACCESS_TOKEN_URL = 'https://api.weixin.qq.com/cgi-bin/token';

    public function __construct($config)
    {
        $this->config = $config;
        $this->setLogger();
    }

    public function setLogger()
    {
        $logger = new Logger('WeixinShareSDK');
        $logger->pushHandler(new StreamHandler(ServiceKernel::instance()->getParameter('kernel.logs_dir').'/share-login.log', Logger::DEBUG));
        $this->logger = $logger;

        return $this;
    }

    public function getAccessToken()
    {
        $params = array(
            'appid' => $this->config['key'],
            'secret' => $this->config['secret'],
            'grant_type' => 'client_credential',
        );
        $result = $this->getRequest(self::ACCESS_TOKEN_URL, $params);

        $rawToken = array();
        $rawToken = json_decode($result, true);

        if (isset($rawToken['errmsg']) && 'ok' != $rawToken['errmsg']) {
            $this->logger && $this->logger->error('WEIXIN_ACCESS_TOKEN_ERROR', $rawToken);

            return array();
        }

        return array(
            'expires_in' => $rawToken['expires_in'],
            'access_token' => $rawToken['access_token'],
        );
    }

    public function getJsApiTicket()
    {
        $token = $this->getAccessToken();

        if (empty($token)) {
            return array();
        }

        $params = array(
            'type' => 'jsapi',
            'access_token' => $token['access_token'],
        );
        $result = $this->getRequest(self::JSAPI_TICKET_URL, $params);

        $rawToken = array();
        $rawToken = json_decode($result, true);

        if (isset($rawToken['errmsg']) && 'ok' != $rawToken['errmsg']) {
            $this->logger && $this->logger->error('WEIXIN_JS_API_TICKET_ERROR', $rawToken);

            return array();
        }

        return array(
            'ticket' => $rawToken['ticket'],
            'expires_in' => $rawToken['expires_in'],
        );
    }

    public function getRequest($url, $params)
    {
        if (!empty($this->mockedRequest)) {
            return $this->mockedRequest;
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

    /**
     * 仅给单元测试mock用。
     */
    public function setRequest(array $request)
    {
        $this->mockedRequest = json_encode($request);
    }
}
