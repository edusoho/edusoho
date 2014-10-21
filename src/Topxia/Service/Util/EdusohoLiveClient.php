<?php

namespace Topxia\Service\Util;

use \RuntimeException;

class EdusohoLiveClient
{
    protected $accessKey;

    protected $secretKey;

    protected $userAgent = 'Edusoho Live Client 1.0';

    protected $connectTimeout = 5;

    protected $timeout = 5;

    protected $apiServer;

    public function __construct (array $options)
    {
        // if (substr($options['apiServer'], 0, 7) != 'http://') {
        //     throw new \RuntimeException('云平台apiServer参数不正确，请更改云视频设置。');
        // }

        /*if (empty($options['accessKey']) or empty($options['secretKey'])) {
            throw new \RuntimeException('云平台accessKey/secretKey不能为空，请更改云视频设置。');
        }*/
        
        $this->apiServer = rtrim($options['apiServer'], '/');
        $this->accessKey = $options['accessKey'];
        $this->secretKey = $options['secretKey'];
    }

    /**
     * 创建直播
     *
     * @param  array  $args 直播参数，支持的参数有：title, speaker, startTime, endTime, authUrl, jumpUrl, errorJumpUrl
     * @return [type]       [description]
     */
    public function createLive(array $args)
    {
        return $this->callRemoteApi('POST', 'LiveCreate', $args);
    }

    public function startLive($liveId)
    {
        $args = array(
            'liveId' => $liveId
        );
        return $this->callRemoteApi('POST', 'LiveStart', $args);
    }

    public function deleteLive($liveId)
    {
        $args = array(
            'liveId' => $liveId
        );
        return $this->callRemoteApi('POST', 'LiveDelete', $args);
    }

    public function entryLive($liveId, $params)
    {
        $url = "http://webinar.vhall.com/appaction.php?module=inituser&pid={$liveId}&email={$params['email']}&name={$params['nickname']}&k={$params['sign']}";
        return array('url' => $url);
    }

    public function getCapacity()
    {
        $args = array(
            'timestamp' => time() . '',
        );
        return $this->callRemoteApi('GET', 'LiveCapacity', $args);
    }

    public function entryReplay($liveId, $replayId)
    {
        $url = "http://webinar.vhall.com/record.php?module=viewrec&id={$liveId}&rsid={$replayId}";
        return $url;
    }

    public function createReplayList($liveId, $title)
    {
        $args = array(
            "liveId" => $liveId, 
            "title" => $title
        );
        $replayList = $this->callRemoteApi('POST', 'LiveReplayCreate', $args);
        if(array_key_exists("error", $replayList)){
            return $replayList;
        }
        return json_decode($replayList["data"], true);
    }

    private function callRemoteApi($httpMethod, $action, array $args)
    {
        $url = $this->makeApiUrl($action);

        $httpParams = array();
        $httpParams['accessKey'] = $this->accessKey;
        $httpParams['args'] = $args;
        $httpParams['sign'] = hash_hmac('sha1', base64_encode(json_encode($args)), $this->secretKey);

        $result = $this->sendRequest($httpMethod, $url, $httpParams);

        return json_decode($result, true);
    }

    private function makeApiUrl($action)
    {
        return $this->apiServer . '/live_api.php?action=' . $action;
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