<?php
namespace Codeages\RestApiClient\HttpRequest;

use Psr\Log\LoggerInterface;

abstract class HttpRequest
{
    protected $options;

    protected $logger;

    public function __construct($options, LoggerInterface $logger = null, $debug = false)
    {
        $this->options = $options;
        $this->logger = $logger;
        $this->debug = $debug;
    }

    abstract public function request($method, $url, $body, array $header = array(), $requestId);
}