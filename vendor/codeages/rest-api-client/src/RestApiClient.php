<?php
namespace Codeages\RestApiClient;

use Psr\Log\LoggerInterface;
use Codeages\RestApiClient\HttpRequest\HttpRequest;
use Codeages\RestApiClient\HttpRequest\CurlHttpRequest;
use Codeages\RestApiClient\Specification\Specification;
use Codeages\RestApiClient\Exceptions\ResponseException;

class RestApiClient
{
    protected $config;

    protected $debug;

    protected $logger;

    protected $http;

    public function __construct($config, Specification $spec, HttpRequest $http = null, LoggerInterface $logger = null, $debug = false)
    {
        $this->config = array_merge(array(
            'lifetime' => 600
        ), $config);

        $this->spec   = $spec;
        $this->debug  = $debug;
        $this->logger = $logger;

        if (empty($http)) {
            $options = array(
                'userAgent'      => 'Codeages Rest Api Client v1.0.0',
                'connectTimeout' => isset($config['connectTimeout']) ? intval($config['connectTimeout']) : 10,
                'timeout'        => isset($config['timeout']) ? intval($config['timeout']) : 10
            );
            $this->http = new CurlHttpRequest($options, $logger, $debug);
        } else {
            $this->http = $http;
        }
    }

    public function post($uri, array $params = array(), array $header = array())
    {
        return $this->request('POST', $uri, $params, $header);
    }

    public function put($uri, array $params = array(), array $header = array())
    {
        return $this->request('PUT', $uri, $params, $header);
    }

    public function patch($uri, array $params = array(), array $header = array())
    {
        return $this->request('PATCH', $uri, $params, $header);
    }

    public function get($uri, array $params = array(), array $header = array())
    {
        $uri = $uri.(strpos($uri, '?') ? '&' : '?').http_build_query($params);
        return $this->request('GET', $uri, $params, $header);
    }

    public function delete($uri, array $params = array(), array $header = array())
    {
        return $this->request('DELETE', $uri, $params, $header);
    }

    public function request($method, $uri, array $params = array(), array $headers = array())
    {
        $requestId = $this->makeRequestId();
        $url       = $this->makeUrl($uri);
        $body      = ($method == 'GET') || empty($params) ? '' : $this->spec->serialize($params);

        $token   = $this->spec->packToken($this->config, $this->makeSignatureUri($url), $body, time() + $this->config['lifetime'], $requestId);
        $headers = array_merge($this->spec->getHeaders($token, $requestId), $headers);
        if (isset($_SERVER['TRACE_ID']) && $_SERVER['TRACE_ID']) {
            $headers = array_merge($headers, array('TRACE-ID: '.$_SERVER['TRACE_ID']));
        }

        $body = $this->http->request($method, $url, $body, $headers, $requestId);

        $context = array('headers' => $headers, 'body' => $body);

        return $this->spec->unserialize($body);
    }

    protected function makeRequestId()
    {
        return ((string) (microtime(true) * 10000)).substr(md5(uniqid('', true)), -18);
    }

    protected function makeUrl($uri)
    {
        return rtrim($this->config['endpoint'], "\/").$uri;
    }

    protected function makeSignatureUri($url)
    {
        preg_match('/\/\/.*?(\/.*)/', $url, $matches);
        return $matches[1];
    }
}
