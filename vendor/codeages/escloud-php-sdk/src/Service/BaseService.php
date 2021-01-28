<?php

namespace ESCloud\SDK\Service;

use ESCloud\SDK\Auth;
use ESCloud\SDK\HttpClient\Client;
use Psr\Log\LoggerInterface;
use ESCloud\SDK\HttpClient\ClientInterface;
use ESCloud\SDK\Exception\SDKException;
use ESCloud\SDK\HttpClient\Response;
use ESCloud\SDK\Exception\ResponseException;
use ESCloud\SDK;

abstract class BaseService
{
    /**
     * ESCloud auth
     *
     * @var Auth
     */
    protected $auth;

    /**
     * Service options
     *
     * @var array
     */
    protected $options;

    /**
     * Http client
     *
     * @var Client
     */
    private $client;

    /**
     * API host
     *
     * @var string
     */
    protected $host = '';

    /**
     * API leaf host
     *
     * @var string
     */
    protected $leafHost = '';

    protected $service = '';

    /**
     * Logger
     *
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(Auth $auth, array $options = array(), LoggerInterface $logger = null, ClientInterface $client = null)
    {
        $this->auth = $auth;
        $this->logger = $logger;
        $this->client = $client;

        $this->options = $this->filterOptions($options);

        if (!empty($this->options['host'])) {
            $this->host = $options['host'];
        }

        if (!empty($this->options['leafHost'])) {
            $this->leafHost = $options['leafHost'];
        }
    }

    protected function createClient()
    {
        if ($this->client) {
            return $this->client;
        }

        $this->client = new Client(array(), $this->logger);

        return $this->client;
    }

    /**
     * API V2 的统一请求方法
     *
     * @param $method
     * @param $uri
     * @param array $data
     * @param array $headers
     * @param string $node
     * @return mixed
     * @throws ResponseException
     * @throws SDKException
     * @throws SDK\HttpClient\ClientException
     */
    protected function request($method, $uri, array $data = array(), array $headers = array(), $node = 'root')
    {
        $options = array();

        if (!empty($data)) {
            if ('GET' === strtoupper($method) && !empty($data)) {
                $uri = $uri . (strpos($uri, '?') > 0 ? '&' : '?') . http_build_query($data);
            } else {
                if (version_compare(phpversion(), '5.4.0', '>=')) {
                    $options['body'] = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                } else {
                    $options['body'] = json_encode($data);
                }
            }
        }

        if (!isset($headers['Authorization'])) {
            $headers['Authorization'] = $this->auth->makeRequestAuthorization($uri, isset($options['body']) ? $options['body'] : '', 600, true, $this->service);
        }

        if (isset($_SERVER['TRACE_ID']) && $_SERVER['TRACE_ID']) {
            $headers['TRACE-ID'] = $_SERVER['TRACE_ID'];
        }

        $headers['Content-Type'] = 'application/json';
        $options['headers'] = $headers;

        $response = $this->createClient()->request($method, $this->getRequestUri($uri, 'http', $node), $options);

        return $this->extractResultFromResponse($response);
    }

    /**
     * 从Response中抽取API返回结果
     *
     * @param Response $response
     * @return mixed
     * @throws ResponseException
     * @throws SDKException
     */
    protected function extractResultFromResponse(Response $response)
    {
        try {
            $result = SDK\json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            throw new SDKException($e->getMessage() . "(response: {$response->getBody()}");
        }

        $responseCode = $response->getHttpResponseCode();

        if ($responseCode < 200 || $responseCode > 299 || isset($result['error'])) {
            $this->logger && $this->logger->error((string)$response);
            throw new ResponseException($response);
        }

        return $result;
    }

    /**
     * 获得完整的请求地址
     *
     * @param $uri
     * @param string $protocol
     * @param string $node
     * @return string
     * @throws SDKException
     */
    protected function getRequestUri($uri, $protocol = 'http', $node = 'root')
    {
        if (!in_array($protocol, array('http', 'https', 'auto'))) {
            throw new SDKException("The protocol parameter must be in 'http', 'https', 'auto', your value is '{$protocol}'.");
        }

        $host = $this->getHostByNode($node);
        if (is_array($host)) {
            shuffle($host);
            reset($host);
            $host = current($host);
        }

        $host = (string)$host;

        if (!$host) {
            throw new SDKException('API host is not exist or invalid.');
        }

        $uri = ('/' !== substr($uri, 0, 1) ? '/' : '') . $uri;

        return ('auto' == $protocol ? '//' : $protocol . '://') . $host . $uri;
    }

    protected function filterOptions(array $options = array())
    {
        return array_replace(array(
            'host' => '',
        ), $options);
    }

    private function getHostByNode($node)
    {
        if ('leaf' == $node) {
            return empty($this->leafHost) ? $this->host : $this->leafHost;
        }

        return $this->host;
    }
}
