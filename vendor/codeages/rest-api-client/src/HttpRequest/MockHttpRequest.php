<?php
namespace Codeages\RestApiClient\HttpRequest;

use Codeages\RestApiClient\Exceptions\ServerException;
use Codeages\RestApiClient\Exceptions\ResponseException;

class MockHttpRequest extends HttpRequest
{
    protected $callbacks;

    public function request($method, $url, $body, array $header = array(), $requestId = '')
    {
        $callback = array_shift($this->callbacks);

        if (is_callable($callback)) {
            return $callback();
        }

        return $callback;
    }

    public function mock($callback)
    {
        $this->callbacks[] = $callback;
    }
}