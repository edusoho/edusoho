<?php
namespace Topxia\Service\CloudPlatform\Client;

use Topxia\System;

class EduSohoOpenClient 
{

    protected $userAgent = 'Open Edusoho App Client 1.0';

    protected $connectTimeout = 5;

    protected $timeout = 5;

    private $apiUrl = 'http://open.edusoho.com/api/v1';

    private $debug = false;

    public function __construct (array $options)
    {
        $this->accessKey = empty($options['accessKey']) ? 'Anonymous' : $options['accessKey'];
        $this->secretKey = empty($options['secretKey']) ? '' : $options['secretKey'];

        if (!empty($options['apiUrl'])) {
            $this->apiUrl = $options['apiUrl'];
        }
        $this->debug = empty($options['debug']) ? false : true;
        $this->tmpDir = empty($options['tmpDir']) ? sys_get_temp_dir() : $options['tmpDir'];
    }

    public function getCloudNotices()
    {
        $args = array();
        return $this->callRemoteApi('GET', '/cloud/notice', $args);
    }

    public function getEsSignal()
    {
        $args = array();
        return $this->callRemoteApi('GET', '/edusoho/signal', $args);
    }

    private function callRemoteApi($httpMethod, $action, array $args)
    {
        list($url, $httpParams) = $this->assembleCallRemoteApiUrlAndParams($action, $args);
        $result = $this->sendRequest($httpMethod, $url, $httpParams);

        return json_decode($result, true);
    }

    private function assembleCallRemoteApiUrlAndParams($action, array $args)
    {
        $url = "{$this->apiUrl}{$action}";
        $edusoho = array(
            'host' => $_SERVER['HTTP_HOST'],
            'version' => System::VERSION, 
            'debug' => $this->debug ? '1' : '0',
        );
        $args['_edusoho'] = $edusoho;

        $httpParams = array();
        $httpParams['accessKey'] = $this->accessKey;
        $httpParams['args'] = $args;
        $httpParams['sign'] = hash_hmac('sha1', base64_encode(json_encode($args)), $this->secretKey);

        return array($url, $httpParams);
    }

     private function sendRequest($method, $url, $params = array())
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_USERAGENT, $this->userAgent);

        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);

        if (strtoupper($method) == 'POST') {
            curl_setopt($curl, CURLOPT_POST, 1);
            $params = http_build_query($params);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        } else {
            if (!empty($params)) {
                $url = $url . (strpos($url, '?') ? '&' : '?') . http_build_query($params);
            }
        }
        curl_setopt($curl, CURLOPT_URL, $url );

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }
}