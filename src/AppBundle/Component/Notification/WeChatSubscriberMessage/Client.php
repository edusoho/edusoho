<?php

namespace AppBundle\Component\Notification\WeChatSubscriberMessage;

use Biz\Common\JsonLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Topxia\Service\Common\ServiceKernel;

class Client
{
    const MESSAGE_SEND = 'cgi-bin/message/subscribe/bizsend';   // POST

    const ACCESS_TOKEN_GET = 'cgi-bin/token';   // GET

    protected $baseUrl = 'https://api.weixin.qq.com';

    /**
     * @var Logger
     */
    protected $logger = null;

    protected $config;

    protected $appId;

    protected $userAgent = 'EduSoho Client 1.0';

    protected $connectTimeout = 30;

    protected $timeout = 30;

    protected $accessToken = '';

    protected $request;

    public function __construct($config)
    {
        $this->config = $config;
        $this->appId = $config['key'];
        $this->setLogger();
    }

    public function getAppId()
    {
        return $this->appId;
    }

    public function setLogger()
    {
        $stream = new StreamHandler(ServiceKernel::instance()->getParameter('kernel.logs_dir').'/template-message.log', Logger::DEBUG);
        $logger = new JsonLogger('WeChatTemplateMessage', $stream);
        $this->logger = $logger;
    }

    public function setAccessToken($token)
    {
        $this->accessToken = $token;
    }

    public function getAccessToken()
    {
        $params = [
            'appid' => $this->config['key'],
            'secret' => $this->config['secret'],
            'grant_type' => 'client_credential',
        ];
        $result = $this->getRequest($this->baseUrl.'/'.self::ACCESS_TOKEN_GET, $params);

        $rawToken = json_decode($result, true);

        if (isset($rawToken['errmsg']) && 'ok' != $rawToken['errmsg']) {
            $this->logger && $this->logger->error('WECHAT_ACCESS_TOKEN_ERROR', ['params' => $params, 'error' => $rawToken]);

            return [];
        }

        return [
            'expires_in' => $rawToken['expires_in'],
            'access_token' => $rawToken['access_token'],
        ];
    }

    public function sendMessage($to, $templateId, $data, $options = [])
    {
        $params = [
            'touser' => $to,
            'template_id' => $templateId,
            'data' => $data,
        ];

        if (!empty($options['url'])) {
            $params['page'] = $options['url'];
        }

        if (!empty($options['miniprogram'])) {
            $params['miniprogram'] = $options['miniprogram'];
        }

        $result = $this->postRequest($this->baseUrl.'/'.self::MESSAGE_SEND, $params);

        $rawResult = json_decode($result, true);

        if (isset($rawResult['errmsg']) && 'ok' != $rawResult['errmsg']) {
            $this->logger && $this->logger->error('WECHAT_SEND_MESSAGE_ERROR', ['params' => $params, 'error' => $rawResult]);
        }

        return $rawResult;
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

        $params['access_token'] = $this->accessToken;

        $url = $url.'?'.http_build_query($params);

        curl_setopt($curl, CURLOPT_URL, $url);

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }

    public function postRequest($url, $params)
    {
        if (isset($this->request)) {
            return $this->request->postRequest($url, $params);
        }

        $curl = curl_init();
        $params = json_encode($params);

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: '.strlen($params),
        ]);
        curl_setopt($curl, CURLOPT_URL, $url.'?'.http_build_query(['access_token' => $this->accessToken]));

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }
}
