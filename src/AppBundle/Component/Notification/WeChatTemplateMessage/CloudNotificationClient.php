<?php

namespace AppBundle\Component\Notification\WeChatTemplateMessage;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Topxia\Service\Common\ServiceKernel;
use QiQiuYun\SDK\Auth;

class CloudNotificationClient
{
    const WECHAT_NOTIFICATION_OPEN = '/accounts';

    const WECHAT_NOTIFICATION_CLOSE = '/accounts/wechat';

    const NOTIFICATIONS_SEND = '/notifications';

    const NOTIFICATION_RESULT_GET = '/notifications/';

    const NOTIFICATIONS_RESULT_BATCH_GET = '/notifications';

    protected $accessKey;

    protected $secretKey;

    protected $appId;

    protected $secret;

    protected $logger = null;

    protected $baseUrl = 'http://notification-service.cn';

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
        $this->appId = $options['app_id'];
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

    public function openWechatNotification()
    {
        $config = array('app_id' => $this->appId, 'app_secret' => $this->secret, 'type' => 'wechat');
        $result = $this->request('POST', self::WECHAT_NOTIFICATION_OPEN, $config);
        $rawResult = json_decode($result, true);

        if (!empty($rawResult['error'])) {
            $this->logger && $this->logger->error('WECHAT_NOTIFICATION_OPEN_ERROR', $rawResult);

            return array();
        }

        return $rawResult;
    }

    public function closeWechatNotification()
    {
        $result = $this->request('DELETE', self::WECHAT_NOTIFICATION_CLOSE);
        $rawResult = json_decode($result, true);

        if (!empty($rawResult['error'])) {
            $this->logger && $this->logger->error('WECHAT_NOTIFICATION_CLOSE_ERROR', $rawResult);

            return array();
        }

        return $rawResult;
    }

    public function sendWechatNotificaion($list)
    {
        $result = $this->request('POST', self::NOTIFICATIONS_SEND, $list);
        $rawResult = json_decode($result, true);

        if (!empty($rawResult['error'])) {
            $this->logger && $this->logger->error('WECHAT_NOTIFICATION_SEND_ERROR', $rawResult);

            return array();
        }

        return $rawResult;
    }

    public function getNotificationSendResult($sn)
    {
        $result = $this->request('GET', self::NOTIFICATION_RESULT_GET.$sn);
        $rawResult = json_decode($result, true);

        if (!empty($rawResult['error'])) {
            $this->logger && $this->logger->error('NOTIFICATION_RESULT_GET_ERROR', $rawResult);

            return array();
        }

        return $rawResult;
    }

    public function batchGetNotificationsSendResult()
    {
        $result = $this->request('GET', self::NOTIFICATIONS_RESULT_BATCH_GET);
        $rawResult = json_decode($result, true);

        if (!empty($rawResult['error'])) {
            $this->logger && $this->logger->error('NOTIFICATION_RESULT_BATCH_GET_ERROR', $rawResult);

            return array();
        }

        return $rawResult;
    }

    public function request($method, $uri, $params = array())
    {
        $method = strtoupper($method);
        $curl = curl_init();
        $url = $this->baseUrl.$uri;
        if ('GET' == $method) {
            $url = empty($params) ? $url : $url.'?'.http_build_query($params);
        } else {
            if (version_compare(phpversion(), '5.4.0', '>=')) {
                $body = empty($params) ? '' : json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            } else {
                $body = empty($params) ? '' : json_encode($params);
            }
            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        }

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: '.$this->auth->makeRequestAuthorization($uri, isset($body) ? $body : ''),
        ));
        curl_setopt($curl, CURLOPT_URL, $url);

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }
}
