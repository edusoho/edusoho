<?php

namespace AppBundle\Component\Notification\WeChatTemplateMessage;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Topxia\Service\Common\ServiceKernel;

class Client
{
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

    protected $userAgent = 'EduSoho Client 1.0';

    protected $connectTimeout = 30;

    protected $timeout = 30;

    public function __construct($config)
    {
        $this->config = $config;
        $this->setLogger();
    }

    public function setLogger()
    {
        $logger = new Logger('WeChatTemplateMessage');
        $logger->pushHandler(new StreamHandler(ServiceKernel::instance()->getParameter('kernel.logs_dir').'/template-message.log', Logger::DEBUG));
        $this->logger = $logger;
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

        if (isset($rawToken['errmsg']) && $rawToken['errmsg'] != 'ok') {
            $this->logger && $this->logger->error('WEIXIN_ACCESS_TOKEN_ERROR', $rawToken);

            return array();
        }

        return array(
            'expires_in' => $rawToken['expires_in'],
            'access_token' => $rawToken['access_token'],
        );
    }

    public function setIndustry($industryOne, $industryTwo)
    {
        $params = [
            'industry_id1' => $industryOne,
            'industry_id2' => $industryTwo,
        ];

        $this->
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


}