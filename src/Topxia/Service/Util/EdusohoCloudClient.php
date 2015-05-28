<?php

namespace Topxia\Service\Util;

use Topxia\Common\ArrayToolkit;
use \RuntimeException;


class EdusohoCloudClient implements CloudClient
{
	protected $accessKey;

	protected $secretKey;

	protected $userAgent = 'EduSoho Cloud Client 1.0';

	protected $connectTimeout = 10;

	protected $timeout = 20;

	protected $apiServer;

    protected $bucket;

    protected $videoCommands = array();

    protected $audioCommands = array();

    protected $pptCommands = array();

    public function __construct (array $options)
    {

        if (empty($options['apiServer'])) {
            $options['apiServer'] = 'http://api.edusoho.net';
        }

    	if (empty($options['accessKey']) or empty($options['secretKey'])) {
    		throw new \RuntimeException('云平台accessKey/secretKey不能为空，请更改云视频设置。');
    	}
    	
    	$this->apiServer = rtrim($options['apiServer'], '/');
    	$this->accessKey = $options['accessKey'];
    	$this->secretKey = $options['secretKey'];
        $this->bucket = $options['bucket'];
        $this->videoCommands = $options['videoCommands'];
        $this->audioCommands = $options['audioCommands'];
        $this->pptCommands = $options['pptCommands'];
    }

    public function makeUploadParams($params)
    {
        $params = ArrayToolkit::parts($params, array(
            'convertor', 'convertCallback', 'convertParams', 'duration', 'user'
        ));
        $params = $this->callRemoteApiWithBase64('GET', 'MakeUploadToken', $params);
        return $params;
    }

	public function generateUploadToken($bucket, array $params = array())
	{
		$cleanParams = array();

		$cleanParams['bucket'] = (string) $bucket;
		if (empty($cleanParams['bucket'])) {
			throw new RuntimeException('bucket不能为空');
		}

		if (!empty($params['duration'])) {
			$cleanParams['duration'] = (int) $params['duration'];
		}

		if (!empty($params['user'])) {
			$cleanParams['user'] = (string) $params['user'];
		}

		if (!empty($params['convertCommands'])) {
			$cleanParams['convertCommands'] = (string) $params['convertCommands'];
		}

		if (!empty($params['convertNotifyUrl'])) {
			$cleanParams['convertNotifyUrl'] = (string) $params['convertNotifyUrl'];
		}

        if (!empty($params['convertor'])) {
            $cleanParams['convertor'] = (string) $params['convertor'];
        }

		$encodedParams = base64_encode(json_encode($cleanParams));

		$sign = hash_hmac('sha1', $encodedParams, $this->secretKey);

		$token = "{$this->accessKey}:{$encodedParams}:{$sign}";

		$content = $this->getRequest($this->getUploadTokenUrl(), array('token' => $token));

		return json_decode($content, true);
	}

	public function download($bucket, $key, $duration = 3600, $asFilename=null)
	{
		$params = array('bucket' => $bucket, 'key' => $key, 'duration' => $duration, 'asFilename' => $asFilename);

        $encodedParams = base64_encode(json_encode($params));

        $sign = hash_hmac('sha1', $encodedParams, $this->secretKey);
        $token = "{$this->accessKey}:{$encodedParams}:{$sign}";

		header("Location: {$this->getDownloadUrl()}?token={$token}");
		exit();
	}

    public function generateHLSUrl($bucket, $key, $duration = 3600)
    {
        $params = array('bucket' => $bucket, 'key' => $key, 'duration' => $duration);

        $encodedParams = base64_encode(json_encode($params));

        $sign = hash_hmac('sha1', $encodedParams, $this->secretKey);
        $token = "{$this->accessKey}:{$encodedParams}:{$sign}";

        $content = $this->getRequest($this->apiServer . '/hls.php', array('token' => $token));

        return json_decode($content, true);
    }

    public function generateHLSQualitiyListUrl($videos, $duration = 3600)
    {
        $url = $this->apiServer . '/api.m3u8?action=HLSQualitiyList';

        $names = array('sd' => '标清', 'hd' => '高清', 'shd' => '超清');
        $bandwidths = array('sd' => '245760', 'hd' => '450560', 'shd' => '655360');

        $items = array();
        foreach (array('sd', 'hd', 'shd') as $type) {
            if (!isset($videos[$type])) {
                continue;
            }

            $items[] = array(
                'name' => $names[$type],
                'bandwidth' => $bandwidths[$type],
                'key' => $videos[$type]['key'],
            );
        }

        $args = array(
            'items' => $items,
            'timestamp' => (string) time(),
            'duration' => (string) $duration
        );

        $httpParams = array();
        $httpParams['accessKey'] = $this->accessKey;
        $httpParams['args'] = $args;
        $httpParams['sign'] = hash_hmac('sha1', base64_encode(json_encode($args)), $this->secretKey);

        $url = $url . (strpos($url, '?') ? '&' : '?') . http_build_query($httpParams);

        return array('url' => $url);
    }

    public function generateFileUrl($bucket, $key, $duration)
    {
        $params = array('bucket' => $bucket, 'key' => $key, 'duration' => $duration);

        $encodedParams = base64_encode(json_encode($params));

        $sign = hash_hmac('sha1', $encodedParams, $this->secretKey);
        $token = "{$this->accessKey}:{$encodedParams}:{$sign}";

        $content = $this->getRequest($this->apiServer . '/file_url.php', array('token' => $token));

        return json_decode($content, true);
    }

    public function getBucket()
    {
        if (empty($this->bucket)) {
            throw new \RuntimeException('云视频bucket不能为空，请更改云视频设置。');
        }
        return $this->bucket;
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

    public function getVideoInfo($bucket, $key)
    {
        $params = array('bucket' => $bucket, 'key' => $key, 'duration' => 3600);

        $encodedParams = base64_encode(json_encode($params));

        $sign = hash_hmac('sha1', $encodedParams, $this->secretKey);
        $token = "{$this->accessKey}:{$encodedParams}:{$sign}";

        $content = $this->getRequest($this->apiServer . '/media_info.php', array('token' => $token));

        $result =  json_decode($content, true);

        if (empty($result)) {
            return null;
        }

        $info = array(
            'duration' => intval($result['format']['duration']),
            'filesize' => intval($result['format']['size'])
        );

        return $info;
    }

    public function getAudioInfo($bucket, $key)
    {
        return $this->getVideoInfo($bucket, $key);
    }

    public function removeFile($key)
    {

    }

    /**
     * 即将废除
     */
    public function deleteFiles(array $keys, array $prefixs = array())
    {
        $args = array();
        $args['keys'] = $keys;
        $args['prefixs'] = $prefixs;

        $args = array_filter($args);

        return $this->callRemoteApi('POST', 'FileDelete', $args);
    }

    public function deleteFilesByKeys($storageType, array $keys)
    {
        $args = array();
        $args['storageType'] = $storageType;
        $args['keys'] = $keys;
        return $this->callRemoteApiWithBase64('POST', 'FilesDeleteByKeys', $args);
    }

    public function deleteFilesByPrefixs($storageType, array $prefixs)
    {
        $args = array();
        $args['storageType'] = $storageType;
        $args['prefixs'] = $prefixs;
        return $this->callRemoteApiWithBase64('POST', 'FilesDeleteByPrefixs', $args);
    }

    public function moveFiles(array $files)
    {
        $args = array();
        $args['moves'] = $files;
        return $this->callRemoteApiWithBase64('POST', 'FileMove', $args);
    }

    public function makeToken($type, array $tokenArgs = array())
    {
        $args = array();
        $args['type'] = $type;
        $args['args'] = $tokenArgs;

        return $this->callRemoteApiWithBase64('POST', 'MakeToken', $args);
    }

    protected function callRemoteApi($httpMethod, $action, array $args)
    {
        $url = $this->makeApiUrl($action);

        $httpParams = array();
        $httpParams['accessKey'] = $this->accessKey;
        $httpParams['args'] = $args;
        $httpParams['sign'] = hash_hmac('sha1', base64_encode(json_encode($args)), $this->secretKey);
        $result = $this->sendRequest($httpMethod, $url, $httpParams);

        return json_decode($result, true);
    }

    protected function callRemoteApiWithBase64($httpMethod, $action, array $args)
    {
        $url = $this->makeApiUrl($action);

        $httpParams = array();
        $httpParams['accessKey'] = $this->accessKey;
        $httpParams['args'] = $this->urlsafeBase64Encode(json_encode($args));
        $httpParams['encode'] = 'base64';
        $httpParams['sign'] = hash_hmac('sha1', base64_encode(json_encode($args)), $this->secretKey);
   
        $result = $this->sendRequest($httpMethod, $url, $httpParams);
    
        return json_decode($result, true);
    }

    protected function urlsafeBase64Encode($string)
    {
        $find = array('+', '/');
        $replace = array('-', '_');
        return str_replace($find, $replace, base64_encode($string));
    }

    public function getFileUrl($key,$targetId,$targetType){
        
    }

    public function getBills($bucket)
    {

        $params = array('bucket' => $bucket);
        $encodedParams = base64_encode(json_encode($params));

        $sign = hash_hmac('sha1', $encodedParams, $this->secretKey);
        $token = "{$this->accessKey}:{$encodedParams}:{$sign}";

        $content = $this->getRequest($this->getBillUrl(), array('token' => $token));

        return json_decode($content, true);
    }

    public function convertVideo($bucket, $key, $commands, $notifyUrl)
    {
        $params = array('bucket' => $bucket, 'key' => $key, 'commands' => $commands, 'notifyUrl' => $notifyUrl);
        $encodedParams = base64_encode(json_encode($params));

        $sign = hash_hmac('sha1', $encodedParams, $this->secretKey);
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
        $args = array();
        $args['_t'] = time();
        return $this->callRemoteApiWithBase64('GET', 'CheckKey', $args);
    }

    public function convertPPT($key, $notifyUrl = null)
    {
        $args = array();
        $args['key'] = $key;
        if ($notifyUrl) {
            $args['notifyUrl'] = $notifyUrl;
        }
            
        return $this->callRemoteApi('GET', 'Pdf2Jpg', $args);
    }

    public function pptImages($key, $length)
    {
        $args = array();
        $args['key'] = $key;
        $args['length'] = $length;
        return $this->callRemoteApi('GET', 'PPTImages', $args);
    }

    public function getMediaInfo($key, $mediaType)
    {
        $args = array();
        $args['key'] = $key;
        $args['duration'] = "3600";
        return json_decode($this->callRemoteApi('GET', 'GetMediaInfo', $args),true);
    }

    private function generateViewToken($bucket, $key)
    {
        $params = array('bucket' => $bucket, 'key' => $key);
        $encodedParams = base64_encode(json_encode($params));

        $sign = hash_hmac('sha1', $encodedParams, $this->secretKey);
        $token = "{$this->accessKey}:{$encodedParams}:{$sign}";

        $content = $this->getRequest($this->getViewTokenUrl(), array('token' => $token));

        return json_decode($content, true);
    }

    private function getUploadTokenUrl()
    {
    	return $this->apiServer . '/upload_token.php';
    }

    private function getViewTokenUrl()
    {
        return $this->apiServer . '/view_token.php';
    }

    private function getDownloadUrl()
    {
    	return $this->apiServer . '/private_download.php';
    }

    private function getBillUrl()
    {
        return $this->apiServer . '/bill.php';
    }

    private function getConvertUrl()
    {
        return $this->apiServer . '/convert.php';
    }

    private function makeApiUrl($action)
    {
        return $this->apiServer . '/api.php?action=' . $action;
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

    /**
     * @todo remove it.
     */
    private function getRequest($url, $params = array(), $cookie = array())
    {

    	$curl = curl_init();

    	curl_setopt($curl, CURLOPT_USERAGENT, $this->userAgent);

		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
		curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_HEADER, 0);

        if (!empty($params)) {
        	$url = $url . '?' . http_build_query($params);
        }

        if ($cookie) {
            $cookie = "{$cookie['name']}={$cookie['value']}";
            curl_setopt($curl, CURLOPT_COOKIE, $cookie);
        }

		curl_setopt($curl, CURLOPT_URL, $url );
		$response = curl_exec($curl);

		return $response;
    }

}