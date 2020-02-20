<?php

namespace ESCloud\SDK\HttpClient;

interface ClientInterface
{
    /**
     * 发起请求
     *
     * @param string $method  请求的方法（GET, POST, PUT, DELETE...)
     * @param string $uri     请求地址
     * @param array  $options 请求的参数选项
     *
     * @return Response
     */
    public function request($method, $uri = '', array $options = array());
}
