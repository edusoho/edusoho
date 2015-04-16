<?php
namespace Topxia\Service\CloudPlatform\Client;

class CloudAPI
{
    const VERSION = 'v1';

    protected $userAgent = 'EduSoho Cloud API Client 1.0';

    protected $connectTimeout = 10;

    protected $timeout = 20;

    private $apiUrl = 'http://api.edusoho.net';

    private $debug = false;

    public function __construct(array $options)
    {
        $this->accessKey = $options['accessKey'];
        $this->secretKey = $options['secretKey'];

        if (!empty($options['apiUrl'])) {
            $this->apiUrl = rtrim($options['apiUrl'], '/');
        }
        $this->debug = empty($options['debug']) ? false : true;
    }

    public function post($uri, array $params = array(), array $header = array())
    {
        return $this->_request('POST', $uri, $params, $header);
    }

    public function put($uri, array $params = array(), array $header = array())
    {
        return $this->_request('PUT', $uri, $params, $header);
    }

    public function patch($uri, array $params = array(), array $header = array())
    {
        return $this->_request('PATCH', $uri, $params, $header);
    }

    public function get($uri, array $params = array(), array $header = array())
    {
        return $this->_request('GET', $uri, $params, $header);
    }

    public function delete($uri, array $params = array(), array $header = array())
    {
        return $this->_request('DELETE', $uri, $params, $header);
    }

    private function _request($method, $uri, $params, $headers)
    {
        $url = $this->apiUrl . '/' . self::VERSION . $uri;
        $headers[] = 'Content-type: application/json';

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_USERAGENT, $this->userAgent);

        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);

        if ($method == 'POST') {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params));
        } else if ($method == 'PUT') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params));
        } else if ($method == 'DELETE') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params));
        } else if ($method == 'PATCH') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params));
        } else {
            if (!empty($params)) {
                $url = $url . (strpos($url, '?') ? '&' : '?') . http_build_query($params);
            }
        }

        $headers[] = 'Auth-Token: ' . $this->_makeAuthToken($url, $method == 'GET' ? array() : $params);

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_URL, $url);

        $response = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($response, true);

        if (empty($result)) {
            throw new \RuntimeException("Response json decode error:<br> $response");
        }

        return $result;
    }

    private function _makeAuthToken($url, $params)
    {
        $matched = preg_match('/:\/\/.*?(\/.*)$/', $url, $matches);
        if (!$matched) {
            throw new \RuntimeException('Make AuthToken Error.');
        }

        $text = $matches[1] . "\n" . json_encode($params) . "\n" . $this->secretKey;

        $hash = md5($text);

        return "{$this->accessKey}:{$hash}";
    }

}
