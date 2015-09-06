<?php
namespace Topxia\Service\CloudPlatform\Client;

use Psr\Log\LoggerInterface;

class CloudAPI extends AbstractCloudAPI
{
    const VERSION = 'v1';

    protected $userAgent = 'EduSoho Cloud API Client 1.0';

    protected $connectTimeout = 30;

    protected $timeout = 60;

    protected $apiUrl = 'http://api.edusoho.net';

    protected $debug = false;

    protected $logger = null;

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

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    protected function _request($method, $uri, $params, $headers)
    {
        $requestId = substr(md5(uniqid('', true)), -16);

        $url = $this->apiUrl . '/' . self::VERSION . $uri;
        $this->debug && $this->logger && $this->logger->debug("[{$requestId}] {$method} {$url}", array('params' => $params, 'headers' => $headers));

        $headers[] = 'Content-type: application/json';

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_USERAGENT, $this->userAgent);

        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 1);

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
        $headers[] = 'API-REQUEST-ID: '. $requestId;

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_URL, $url);

        $response = curl_exec($curl);
        $curlinfo = curl_getinfo($curl);
        $header = substr($response, 0, $curlinfo['header_size']);
        $body = substr($response, $curlinfo['header_size']);

        $this->debug && $this->logger && $this->logger->debug("[{$requestId}] CURL_INFO", $curlinfo);
        $this->debug && $this->logger && $this->logger->debug("[{$requestId}] RESPONSE_HEADER {$header}");
        $this->debug && $this->logger && $this->logger->debug("[{$requestId}] RESPONSE_BODY {$body}");

        curl_close($curl);
        $result = json_decode($body, true);
        if ($result === null) {
            $context = array(
                'CURLINFO' => $curlinfo,
                'HEADER' => $header,
                'BODY' => $body,
            );
            $this->logger && $this->logger->error("[{$requestId}] RESPONSE_JSON_DECODE_ERROR", $context);
            throw new \RuntimeException("Response json decode error:<br> $response");
        }

        return $result;
    }

    protected function _makeAuthToken($url, $params)
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
