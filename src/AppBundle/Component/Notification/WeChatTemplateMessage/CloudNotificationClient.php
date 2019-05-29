<?php

namespace AppBundle\Component\Notification\WeChatTemplateMessage;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Topxia\Service\Common\ServiceKernel;
use QiQiuYun\SDK\Auth;

class CloudNotificationClient
{
    const WECHAT_CONFIG_OPEN = '/wechat_config/open';

    const WECHAT_CONFIG_CLOSE = '/wechat_config/close';

    const NOTIFICATIONS_SEND = '/wechat_notifications';

    const NOTIFICATION_RESULT_GET = '/wechat_notifications/';

    const NOTIFICATIONS_RESULT_BATCH_GET = '/wechat_notifications';

    protected $accessKey;

    protected $secretKey;

    protected $appId;

    protected $secret;

    protected $logger = null;

    protected $baseUrl = '';

    protected $config;

    protected $userAgent = 'EduSoho Cloud API Client 1.0';

    protected $connectTimeout = 30;

    protected $timeout = 30;

    protected $auth;

    public function __construct($options)
    {
        $this->setKey($options);
        $this->setAuth();
        $this->setLogger();
    }

    public function setKey($options)
    {
        $this->accessKey = $options['accessKey'];
        $this->secretKey = $options['secretKey'];
        $this->appId = $options['appId'];
        $this->secret = $options['secret'];
    }

    public function setAuth()
    {
        $this->auth = new Auth($this->accessKey, $this->secretKey);
    }

    public function setLogger()
    {
        $logger = new Logger('WeChatTemplateMessage');
        $logger->pushHandler(new StreamHandler(ServiceKernel::instance()->getParameter('kernel.logs_dir').'/template-message.log', Logger::DEBUG));
        $this->logger = $logger;
    }

    public function openWechatConfig()
    {
        $config = array('appId' => $this->appId, 'secret' => $this->secret);
        $result = $this->postRequest(self::WECHAT_CONFIG_OPEN, $config);
        $rawResult = json_decode($result, true);

        if (isset($rawResult['success'] && false == $rawResult['success'])) {
            $this->logger && $this->logger->error('WECHAT_CONFIG_OPEN_ERROR', $rawResult);

            return array();
        }

        return $rawResult;
    }

    public function closeWechatConfig()
    {
        $result = $this->postRequest(self::WECHAT_CONFIG_CLOSE);
        $rawResult = json_decode($result, true);

        if (isset($rawResult['success'] && false == $rawResult['success'])) {
            $this->logger && $this->logger->error('WECHAT_CONFIG_CLOSE_ERROR', $rawResult);

            return array();
        }

        return $rawResult;
    }

    public function sendWechatNotificaion($list)
    {
        $result = $this->postRequest(self::NOTIFICATIONS_SEND, $list);
        $rawResult = json_decode($result, true);

        if (isset($rawResult['success'] && false == $rawResult['success'])) {
            $this->logger && $this->logger->error('WECHAT_NOTIFICATION_SEND_ERROR', $rawResult);

            return array();
        }

        return $rawResult;
    }

    public function getNotificationSendResult($batchId)
    {
        $result = $this->getRequest(self::NOTIFICATION_RESULT_GET.$batchId);
        $rawResult = json_decode($result, true);

        if (isset($rawResult['success'] && false == $rawResult['success'])) {
            $this->logger && $this->logger->error('NOTIFICATION_RESULT_GET_ERROR', $rawResult);

            return array();
        }

        return $rawResult;
    }

    public function batchGetNotificationsSendResult()
    {
        $result = $this->getRequest(self::NOTIFICATIONS_RESULT_BATCH_GET);
        $rawResult = json_decode($result, true);

        if (isset($rawResult['success'] && false == $rawResult['success'])) {
            $this->logger && $this->logger->error('NOTIFICATION_RESULT_BATCH_GET_ERROR', $rawResult);

            return array();
        }

        return $rawResult;
    }

    public function getRequest($uri, $params = array())
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: '.$this->auth->makeRequestAuthorization($uri, ''),
        ));

        $url = $this->baseUrl.$uri.'?'.http_build_query($params);

        curl_setopt($curl, CURLOPT_URL, $url);

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }

    public function postRequest($uri, $params = array())
    {
        $curl = curl_init();
        $params = json_encode($params);
        if (version_compare(phpversion(), '5.4.0', '>=')) {
            $body = empty($params) ? '' : json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } else {
            $body = empty($params) ? '' : json_encode($params);
        }

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: '.strlen($params),
            'Authorization: '.$this->auth->makeRequestAuthorization($uri, $body),
        ));
        curl_setopt($curl, CURLOPT_URL, $this->baseUrl.$uri);

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }
}