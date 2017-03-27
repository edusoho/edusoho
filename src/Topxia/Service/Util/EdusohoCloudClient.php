<?php

namespace Topxia\Service\Util;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;

class EdusohoCloudClient extends BaseService implements CloudClient
{
    protected $accessKey;

    protected $secretKey;

    protected $userAgent = 'EduSoho Cloud Client 1.0';

    protected $connectTimeout = 10;

    protected $timeout = 20;

    protected $apiServer;

    protected $videoCommands = array();

    protected $audioCommands = array();

    protected $pptCommands = array();

    public function __construct(array $options)
    {
        if (empty($options['apiServer'])) {
            $options['apiServer'] = 'http://api.edusoho.net';
        }

        if (empty($options['accessKey']) || empty($options['secretKey'])) {
            throw new \RuntimeException('云平台accessKey/secretKey不能为空，请更改云视频设置。');
        }

        $this->apiServer = rtrim($options['apiServer'], '/');
        $this->accessKey = $options['accessKey'];
        $this->secretKey = $options['secretKey'];

        if (isset($options['videoCommands'])) {
            $this->videoCommands = $options['videoCommands'];
        }
        if (isset($options['audioCommands'])) {
            $this->audioCommands = $options['audioCommands'];
        }
        if (isset($options['pptCommands'])) {
            $this->pptCommands = $options['pptCommands'];
        }
    }

    public function makeUploadParams($params)
    {
        $params = ArrayToolkit::parts($params, array(
            'convertor', 'convertCallback', 'convertParams', 'duration', 'user'
        ));
        $params = $this->callRemoteApiWithBase64('GET', 'MakeUploadToken', $params);
        return $params;
    }

    public function generateHLSQualitiyListUrl($videos, $duration = 3600)
    {
        $url = $this->apiServer.'/api.m3u8?action=HLSQualitiyList';

        $names      = array('sd' => '标清', 'hd' => '高清', 'shd' => '超清');
        $bandwidths = array('sd' => '245760', 'hd' => '450560', 'shd' => '655360');

        $items = array();

        foreach (array('sd', 'hd', 'shd') as $type) {
            if (!isset($videos[$type])) {
                continue;
            }

            $items[] = array(
                'name'      => $names[$type],
                'bandwidth' => $bandwidths[$type],
                'key'       => $videos[$type]['key']
            );
        }

        $args = array(
            'items'     => $items,
            'timestamp' => (string) time(),
            'duration'  => (string) $duration
        );

        $httpParams              = array();
        $httpParams['accessKey'] = $this->accessKey;
        $httpParams['args']      = $args;
        $httpParams['sign']      = hash_hmac('sha1', base64_encode(json_encode($args)), $this->secretKey);

        $url = $url.(strpos($url, '?') ? '&' : '?').http_build_query($httpParams);

        return array('url' => $url);
    }

    /**
     * update
     */
    public function generateFileUrl($key, $duration)
    {
        $params = array('key' => $key, 'duration' => $duration);

        $encodedParams = base64_encode(json_encode($params));

        $sign  = hash_hmac('sha1', $encodedParams, $this->secretKey);
        $token = "{$this->accessKey}:{$encodedParams}:{$sign}";

        $content = $this->getRequest($this->apiServer.'/file_url.php', array('token' => $token));

        return json_decode($content, true);
    }

    public function getVideoConvertCommands()
    {
        return $this->videoCommands;
    }

    public function getAudioConvertCommands()
    {
        return $this->audioCommands;
    }

    public function getPPTConvertCommands()
    {
        return $this->pptCommands;
    }

    public function getAudioInfo($key)
    {
        return $this->getVideoInfo($key);
    }

    public function removeFile($key)
    {
    }

    /**
     * 即将废除
     */
    public function deleteFiles(array $keys, array $prefixs = array())
    {
        $args            = array();
        $args['keys']    = $keys;
        $args['prefixs'] = $prefixs;

        $args = array_filter($args);

        return $this->callRemoteApi('POST', 'FileDelete', $args);
    }

    public function deleteFilesByKeys($storageType, array $keys)
    {
        $args                = array();
        $args['storageType'] = $storageType;
        $args['keys']        = $keys;
        return $this->callRemoteApiWithBase64('POST', 'FilesDeleteByKeys', $args);
    }

    public function deleteFilesByPrefixs($storageType, array $prefixs)
    {
        $args                = array();
        $args['storageType'] = $storageType;
        $args['prefixs']     = $prefixs;
        return $this->callRemoteApiWithBase64('POST', 'FilesDeleteByPrefixs', $args);
    }

    public function moveFiles(array $files)
    {
        $args          = array();
        $args['moves'] = $files;
        return $this->callRemoteApiWithBase64('POST', 'FileMove', $args);
    }

    public function makeToken($type, array $tokenArgs = array())
    {
        $args         = array();
        $args['type'] = $type;
        $args['args'] = $tokenArgs;

        return $this->callRemoteApiWithBase64('POST', 'MakeToken', $args);
    }

    protected function callRemoteApi($httpMethod, $action, array $args)
    {
        $url = $this->makeApiUrl($action);

        $httpParams              = array();
        $httpParams['accessKey'] = $this->accessKey;
        $httpParams['args']      = $args;
        $httpParams['sign']      = hash_hmac('sha1', base64_encode(json_encode($args)), $this->secretKey);
        $result                  = $this->sendRequest($httpMethod, $url, $httpParams);

        return json_decode($result, true);
    }

    protected function callRemoteApiWithBase64($httpMethod, $action, array $args)
    {
        $url = $this->makeApiUrl($action);

        $httpParams              = array();
        $httpParams['accessKey'] = $this->accessKey;
        $httpParams['args']      = $this->urlsafeBase64Encode(json_encode($args));
        $httpParams['encode']    = 'base64';
        $httpParams['sign']      = hash_hmac('sha1', base64_encode(json_encode($args)), $this->secretKey);

        $result = $this->sendRequest($httpMethod, $url, $httpParams);

        return json_decode($result, true);
    }

    protected function urlsafeBase64Encode($string)
    {
        $find    = array('+', '/');
        $replace = array('-', '_');
        return str_replace($find, $replace, base64_encode($string));
    }

    public function getFileUrl($key, $targetId, $targetType)
    {
    }

    public function getBills()
    {
        $encodedParams = base64_encode(json_encode(array()));

        $sign  = hash_hmac('sha1', $encodedParams, $this->secretKey);
        $token = "{$this->accessKey}:{$encodedParams}:{$sign}";

        $content = $this->getRequest($this->getBillUrl(), array('token' => $token));

        return json_decode($content, true);
    }

    public function convertVideo($key, $commands, $notifyUrl)
    {
        $params        = array('key' => $key, 'commands' => $commands, 'notifyUrl' => $notifyUrl);
        $encodedParams = base64_encode(json_encode($params));

        $sign  = hash_hmac('sha1', $encodedParams, $this->secretKey);
        $token = "{$this->accessKey}:{$encodedParams}:{$sign}";

        $content = $this->getRequest($this->getConvertUrl(), array('token' => $token));

        return json_decode($content, true);
    }

    public function reconvertFile($key, $params)
    {
        $params['key'] = $key;
        return $this->callRemoteApiWithBase64('POST', 'FileReconvert', $params);
    }

    public function checkKey()
    {
        $args       = array();
        $args['_t'] = time();
        return $this->callRemoteApiWithBase64('GET', 'CheckKey', $args);
    }

    public function convertPPT($key, $notifyUrl = null)
    {
        $args        = array();
        $args['key'] = $key;

        if ($notifyUrl) {
            $args['notifyUrl'] = $notifyUrl;
        }

        return $this->callRemoteApi('GET', 'Pdf2Jpg', $args);
    }

    public function pptImages($key, $length)
    {
        $args           = array();
        $args['key']    = $key;
        $args['length'] = $length;
        return $this->callRemoteApi('GET', 'PPTImages', $args);
    }

    public function getMediaInfo($key, $mediaType)
    {
        $args             = array();
        $args['key']      = $key;
        $args['duration'] = "3600";
        return json_decode($this->callRemoteApi('GET', 'GetMediaInfo', $args), true);
    }

    protected function getBillUrl()
    {
        return $this->apiServer.'/bill.php';
    }

    protected function getConvertUrl()
    {
        return $this->apiServer.'/convert.php';
    }

    protected function makeApiUrl($action)
    {
        return $this->apiServer.'/api.php?action='.$action;
    }

    protected function sendRequest($method, $url, $params = array())
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
                $url = $url.(strpos($url, '?') ? '&' : '?').http_build_query($params);
            }
        }

        curl_setopt($curl, CURLOPT_URL, $url);

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    /**
     * @todo remove it.
     */
    protected function getRequest($url, $params = array(), $cookie = array())
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_USERAGENT, $this->userAgent);

        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);

        if (!empty($params)) {
            $url = $url.'?'.http_build_query($params);
        }

        if ($cookie) {
            $cookie = "{$cookie['name']}={$cookie['value']}";
            curl_setopt($curl, CURLOPT_COOKIE, $cookie);
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        $response = curl_exec($curl);

        return $response;
    }
}
