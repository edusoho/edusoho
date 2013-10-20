<?php

namespace Topxia\Service\Util;

use \RuntimeException;

class EdusohoCloudClient
{
	protected $accessKey;

	protected $secretKey;

	protected $userAgent = 'Edusoho Cloud Client 1.0';

	protected $connectTimeout = 5;

	protected $timeout = 5;

	protected $apiServer;

    public function __construct ($apiServer, $accessKey, $secretKey)
    {
    	$this->apiServer = rtrim($apiServer, '/');
    	$this->accessKey = $accessKey;
    	$this->secretKey = $secretKey;
    }

	public function generateUploadToken($bucket, array $params)
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

	public function download($bucket, $key)
	{
		$params = array('bucket' => $bucket, 'key' => $key);
		$encodedParams = base64_encode(json_encode($params));

		$sign = hash_hmac('sha1', $encodedParams, $this->secretKey);
		$token = "{$this->accessKey}:{$encodedParams}:{$sign}";

		header("Location: {$this->getDownloadUrl()}?token={$token}");
		exit();
	}

    public static function getVideoConvertCommands()
    {
        return array(
            'avthumb/flv/r/24/vb/256k/vcodec/libx264/ar/22050/ab/64k/acodec/libmp3lame' => 'sd',
            'avthumb/flv/r/24/vb/512k/vcodec/libx264/ar/44100/ab/64k/acodec/libmp3lame' => 'hd',
            'avthumb/flv/r/24/vb/1024k/vcodec/libx264/ar/44100/ab/64k/acodec/libmp3lame' => 'shd',
        );
    }

    private function getUploadTokenUrl()
    {
    	return $this->apiServer . '/upload_token.php';
    }

    private function getDownloadUrl()
    {
    	return $this->apiServer . '/download.php';
    }

    private function getRequest($url, $params)
    {

    	$curl = curl_init();

    	curl_setopt($curl, CURLOPT_USERAGENT, $this->userAgent);

		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
		curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_HEADER, 0);

    	$url = $url . '?' . http_build_query($params);
		curl_setopt($curl, CURLOPT_URL, $url );

		$response = curl_exec($curl);

		return $response;
    }

}