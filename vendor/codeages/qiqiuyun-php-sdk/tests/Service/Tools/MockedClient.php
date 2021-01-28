<?php

namespace QiQiuYun\SDK\Tests\Service\Tools;

class MockedClient
{
    protected $response;

    public function request($method, $url, $data)
    {
        $this->method = $method;
        $this->url = $url;
        $this->data = $data;

        return $this->response;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getData()
    {
        return $this->data;
    }
}
