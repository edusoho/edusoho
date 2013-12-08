<?php

namespace Topxia\Service\Util;

use \RuntimeException;


class EdusohoCloudClient implements CloudClient
{
	protected $accessKey;

	protected $secretKey;

	protected $userAgent = 'Edusoho Cloud Client 1.0';

	protected $connectTimeout = 5;

	protected $timeout = 5;

	protected $apiServer;

    protected $bucket;

    protected $videoCommands = array();

    public function __construct (array $options)
    {
    	if (substr($options['apiServer'], 0, 7) != 'http://') {
    		throw new \RuntimeException('云存储apiServer参数不正确，请更改云存储设置。');
    	}

    	if (empty($options['accessKey']) or empty($options['secretKey'])) {
    		throw new \RuntimeException('云存储accessKey/secretKey不能为空，请更改云存储设置。');
    	}
    	
    	$this->apiServer = rtrim($options['apiServer'], '/');
    	$this->accessKey = $options['accessKey'];
    	$this->secretKey = $options['secretKey'];
        $this->bucket = $options['bucket'];
        $this->videoCommands = $options['videoCommands'];
        $this->audioCommands = $options['audioCommands'];
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

    public function getBucket()
    {
        if (empty($this->bucket)) {
            throw new \RuntimeException('云存储bucket不能为空，请更改云存储设置。');
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

    public function getVideoInfo($bucket, $key)
    {
        $token = $this->generateViewToken($bucket, $key);

        $content = $this->getRequest($token['url'] . '?avinfo' , array(), $token['cookie']);
        $result = json_decode($content, true);

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

    public function removeFile($key){

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
    	return $this->apiServer . '/download.php';
    }

    private function getBillUrl()
    {
        return $this->apiServer . '/bill.php';
    }

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