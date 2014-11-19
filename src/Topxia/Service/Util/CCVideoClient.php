<?php

namespace Topxia\Service\Util;

class CCVideoClient
{

	protected $userAgent = 'Topxia OAuth Client 1.0';

	protected $connectTimeout = 30;

	protected $timeout = 30;

	protected $apiUrl = "http://spark.bokecc.com/api/video";

	protected $salt = "zXGXK3h2Yn3IitAd246D7wIY3tOVxL4T";

	public function getRequestUrl($userId,$videoid,$format = 'json')
	{
        return $this->apiUrl."?userid={$userId}&videoid={$videoid}&format=json&time=".time();
	}

    public function getCCHashKey($userId,$videoid)
    {
    	$para = "userid={$userId}&videoid={$videoid}";
        return md5("format=json&".$para.'&'.'time='.time().'&salt='.$this->salt);
    }
    
    public function getRequestCCUrl($userId,$videoid,$format = 'json')
    {
    	$hashkey = $this->getCCHashKey($userId,$videoid);
        return $this->getRequestUrl($userId,$videoid,$format).'&hash='.$hashkey;
    }

	public function getVideoInfo($userId,$videoid,$format = 'json')
	{
        $params['userid'] = $userId;
        $params['videoid'] = $videoid;
        $params['format'] = $format;
        $params['time'] = time();
        $params['hash'] = $this->getCCHashKey($userId,$videoid);

        return $this->getRequest($this->apiUrl, $params);
	}

    private function getRequest($url, $params)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_CAINFO, __DIR__ . '/cacert.pem');

        curl_setopt($curl, CURLOPT_USERAGENT, $this->userAgent);

        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);

        $url = $url . '?' . http_build_query($params);
        curl_setopt($curl, CURLOPT_URL, $url );

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }
}