<?php

namespace Biz\CloudPlatform\Client;

use AppBundle\System;

class EduSohoAppClient implements AppClient
{
    protected $userAgent = 'Open EduSoho App Client 1.0';

    protected $connectTimeout = 5;

    protected $timeout = 5;

    private $apiUrl = 'open.edusoho.com/app_api';

    private $debug = false;

    /**
     * @var string
     */
    private $accessKey;

    /**
     * @var string
     */
    private $secretKey;

    /**
     * tmp dir path.
     *
     * @var string
     */
    private $tmpDir;

    public function __construct(array $options)
    {
        $this->accessKey = empty($options['accessKey']) ? 'Anonymous' : $options['accessKey'];
        $this->secretKey = empty($options['secretKey']) ? '' : $options['secretKey'];

        if (!empty($options['apiUrl'])) {
            $this->apiUrl = $options['apiUrl'];
        } else {
            $protocol = empty($options['isSecure']) ? 'http://' : 'https://';
            $this->apiUrl = $protocol.$this->apiUrl;
        }

        $this->debug = empty($options['debug']) ? false : true;
        $this->tmpDir = empty($options['tmpDir']) ? sys_get_temp_dir() : $options['tmpDir'];
    }

    public function getTokenLoginUrl($routingName, $params)
    {
        $loginToken = $this->getLoginToken();

        $url = str_replace('app_api', '', $this->apiUrl).'token_login?token='.$loginToken['token'].'&goto='.$routingName;

        if (!empty($params)) {
            $url .= '&param='.urldecode(json_encode($params));
        }

        return $url;
    }

    public function getApps()
    {
        $args = array();
        //GetAppCenter
        return $this->callRemoteApi('GET', 'GetAppCenter', $args);
    }

    public function getBinded()
    {
        $args = array();

        return $this->callRemoteApi('GET', 'HasBinded', $args);
    }

    public function getMessages()
    {
        $args = array();

        return $this->callRemoteApi('GET', 'GetMessages', $args);
    }

    public function checkUpgradePackages($apps, $extInfos)
    {
        $args = array('apps' => $apps, 'extInfo' => $extInfos);

        return $this->callRemoteApi('POST', 'CheckUpgradePackages', $args);
    }

    public function submitRunLog($log)
    {
        $args = array('log' => $log);

        return $this->callRemoteApi('POST', 'SubmitRunLog', $args);
    }

    public function downloadPackage($packageId)
    {
        $args = array('packageId' => (string) $packageId);
        list($url, $httpParams) = $this->assembleCallRemoteApiUrlAndParams('DownloadPackage', $args);
        $url = $url.(strpos($url, '?') ? '&' : '?').http_build_query($httpParams);

        return $this->download($url);
    }

    public function checkDownloadPackage($packageId)
    {
        $args = array('packageId' => (string) $packageId);

        return $this->callRemoteApi('GET', 'CheckDownloadPackage', $args);
    }

    public function getPackage($id)
    {
        $args = array('packageId' => (string) $id);

        return $this->callRemoteApi('GET', 'GetPackage', $args);
    }

    public function repairProblem($token)
    {
        $args = array('token' => $token);

        return $this->callRemoteApi('POST', 'RepairProblem', $args);
    }

    public function getLoginToken()
    {
        $args = array();

        return $this->callRemoteApi('POST', 'GetLoginToken', $args);
    }

    public function getAppStatusByCode($code)
    {
        $args = array('appCode' => $code);

        return $this->callRemoteApi('GET', 'GetMyAppStatus', $args);
    }

    protected function callRemoteApi($httpMethod, $action, array $args)
    {
        list($url, $httpParams) = $this->assembleCallRemoteApiUrlAndParams($action, $args);
        $result = $this->sendRequest($httpMethod, $url, $httpParams);
        if (empty($result)) {
            return array();
        }

        return json_decode($result, true);
    }

    protected function assembleCallRemoteApiUrlAndParams($action, array $args)
    {
        $url = "{$this->apiUrl}?action={$action}";
        $edusoho = array(
            'edition' => 'opensource',
            'host' => $_SERVER['HTTP_HOST'],
            'version' => System::VERSION,
            'debug' => $this->debug ? '1' : '0',
        );
        $args['_edusoho'] = $edusoho;

        $httpParams = array();
        $httpParams['accessKey'] = $this->accessKey;
        $httpParams['args'] = $args;
        $httpParams['sign'] = hash_hmac('sha1', base64_encode(json_encode($args)), $this->secretKey);
        if (isset($_SERVER['TRACE_ID']) && $_SERVER['TRACE_ID']) {
            $httpParams['TRACE-ID'] = $_SERVER['TRACE_ID'];
        }

        return array($url, $httpParams);
    }

    protected function download($url)
    {
        $filename = md5($url).'_'.time();
        $filepath = $this->tmpDir.DIRECTORY_SEPARATOR.$filename;

        $fp = fopen($filepath, 'w');

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_FILE, $fp);
        curl_exec($curl);
        curl_close($curl);

        fclose($fp);

        return $filepath;
    }

    protected function sendRequest($method, $url, $params = array())
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_USERAGENT, $this->userAgent);

        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);

        if ('POST' == strtoupper($method)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            $params = http_build_query($params);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        } else {
            if (!empty($params)) {
                $url = $url.(strpos($url, '?') ? '&' : '?').http_build_query($params);
            }
        }

        curl_setopt($curl, CURLOPT_URL, $url);

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }
}
