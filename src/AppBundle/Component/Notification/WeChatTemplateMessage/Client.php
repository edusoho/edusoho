<?php

namespace AppBundle\Component\Notification\WeChatTemplateMessage;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Topxia\Service\Common\ServiceKernel;

class Client
{
    const GET_USER_INFO = 'cgi-bin/user/info';

    const BATCH_GET_USER_INFO = 'cgi-bin/user/info/batchget'; //POST

    const GET_USER_LIST = 'cgi-bin/user/get'; //GET

    const INDUSTRY_SET = 'cgi-bin/template/api_set_industry';

    const INDUSTRY_GET = 'cgi-bin/template/get_industry';

    const TEMPLATE_ADD = 'cgi-bin/template/api_add_template';

    const TEMPLATE_DEL = 'cgi-bin/template/del_private_template';

    const TEMPLATE_LIST = 'cgi-bin/template/get_all_private_template';

    const MESSAGE_SEND = 'cgi-bin/message/template/send';

    const ACCESS_TOKEN_GET = 'cgi-bin/token';

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
        $logger = new Logger('WeChatTemplateMessage');
        $logger->pushHandler(new StreamHandler(ServiceKernel::instance()->getParameter('kernel.logs_dir').'/template-message.log', Logger::DEBUG));
        $this->logger = $logger;
    }

    public function setAccessToken($token)
    {
        $this->accessToken = $token;
    }

    public function getAccessToken()
    {
        $params = array(
            'appid' => $this->config['key'],
            'secret' => $this->config['secret'],
            'grant_type' => 'client_credential',
        );
        $result = $this->getRequest($this->baseUrl.'/'.self::ACCESS_TOKEN_GET, $params);

        $rawToken = json_decode($result, true);

        if (isset($rawToken['errmsg']) && 'ok' != $rawToken['errmsg']) {
            $this->logger && $this->logger->error('WECHAT_ACCESS_TOKEN_ERROR', $rawToken);

            return array();
        }

        return array(
            'expires_in' => $rawToken['expires_in'],
            'access_token' => $rawToken['access_token'],
        );
    }

    public function getUserInfo($openId, $lang = 'zh_CN')
    {
        $params = array(
            'openid' => $openId,
            'lang' => $lang,
        );
        $result = $this->getRequest($this->baseUrl.'/'.self::GET_USER_INFO, $params);

        $rawResult = json_decode($result, true);

        if (isset($rawResult['errmsg']) && 'ok' != $rawResult['errmsg']) {
            $this->logger && $this->logger->error('WECHAT_GET_USER_INFO_ERROR', $rawResult);

            return array();
        }

        return $rawResult;
    }

    /**
     * @param $userList
     *
     * @return array
     *               通过已有的openId获取详细信息
     */
    public function batchGetUserInfo($userList)
    {
        if (empty($userList)) {
            return array();
        }

        $params = array(
            'user_list' => $userList,
        );

        $result = $this->postRequest($this->baseUrl.'/'.self::BATCH_GET_USER_INFO, $params);

        $rawResult = json_decode($result, true);

        if (isset($rawResult['errmsg']) && 'ok' != $rawResult['errmsg']) {
            $this->logger && $this->logger->error('WECHAT_BATCH_GET_USER_INFO_ERROR', $rawResult);

            return array();
        }

        return $rawResult['user_info_list'];
    }

    //获取服务号的所有用户，分页
    public function getUserList($nextOpenId = '')
    {
        $params = array();

        if ($nextOpenId) {
            $params['next_openid'] = $nextOpenId;
        }

        $result = $this->getRequest($this->baseUrl.'/'.self::GET_USER_LIST, $params);
        $rawResult = json_decode($result, true);

        if (isset($rawResult['errmsg']) && 'ok' != $rawResult['errmsg']) {
            $this->logger && $this->logger->error('WECHAT_GET_USER_LIST_ERROR', $rawResult);

            return array();
        }

        return $rawResult;
    }

    public function setIndustry($industryOne, $industryTwo)
    {
        $params = array(
            'industry_id1' => $industryOne,
            'industry_id2' => $industryTwo,
        );

        $result = $this->postRequest($this->baseUrl.'/'.self::INDUSTRY_SET, $params);

        $rawResult = json_decode($result, true);

        if (isset($rawResult['errmsg']) && 'ok' != $rawResult['errmsg']) {
            $this->logger && $this->logger->error('WECHAT_SET_INDUSTRY_ERROR', $rawResult);

            return array();
        }

        return $rawResult;
    }

    public function getIndustry()
    {
        $result = $this->getRequest($this->baseUrl.'/'.self::INDUSTRY_GET, array());

        $rawResult = json_decode($result, true);

        if (isset($rawResult['errmsg']) && 'ok' != $rawResult['errmsg']) {
            $this->logger && $this->logger->error('WECHAT_GET_INDUSTRY_ERROR', $rawResult);

            return array();
        }

        return $rawResult;
    }

    public function addTemplate($shortId)
    {
        $params = array(
            'template_id_short' => $shortId,
        );

        $result = $this->postRequest($this->baseUrl.'/'.self::TEMPLATE_ADD, $params);

        $rawResult = json_decode($result, true);

        if (isset($rawResult['errmsg']) && 'ok' != $rawResult['errmsg']) {
            $this->logger && $this->logger->error('WECHAT_ADD_TEMPLATE_ERROR', $rawResult);

            return array();
        }

        return $rawResult;
    }

    public function getTemplateList()
    {
        $result = $this->getRequest($this->baseUrl.'/'.self::TEMPLATE_LIST, array());

        $rawResult = json_decode($result, true);

        if (isset($rawResult['errmsg']) && 'ok' != $rawResult['errmsg']) {
            $this->logger && $this->logger->error('WECHAT_GET_TEMPLATE_LIST_ERROR', $rawResult);

            return array();
        }

        return $rawResult;
    }

    public function deleteTemplate($templateId)
    {
        $params = array(
            'template_id' => $templateId,
        );
        $result = $this->postRequest($this->baseUrl.'/'.self::TEMPLATE_DEL, $params);

        $rawResult = json_decode($result, true);

        if (isset($rawResult['errmsg']) && 'ok' != $rawResult['errmsg']) {
            $this->logger && $this->logger->error('WECHAT_DEL_TEMPLATE_ERROR', $rawResult);

            return array();
        }

        return $rawResult;
    }

    public function sendTemplateMessage($to, $templateId, $data, $options = array())
    {
        $params = array(
            'touser' => $to,
            'template_id' => $templateId,
            'data' => $data,
        );

        if (!empty($options['url'])) {
            $params['url'] = $options['url'];
        }

        if (!empty($options['miniprogram'])) {
            $params['miniprogram'] = $options['miniprogram'];
        }

        $result = $this->postRequest($this->baseUrl.'/'.self::MESSAGE_SEND, $params);

        $rawResult = json_decode($result, true);

        if (isset($rawResult['errmsg']) && 'ok' != $rawResult['errmsg']) {
            $this->logger && $this->logger->error('WECHAT_SEND_MESSAGE_ERROR', $rawResult);

            return array();
        }

        return $rawResult;
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
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: '.strlen($params),
        ));
        curl_setopt($curl, CURLOPT_URL, $url.'?'.http_build_query(array('access_token' => $this->accessToken)));

        // curl_setopt($curl, CURLINFO_HEADER_OUT, TRUE );

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }
}
