<?php
namespace Codeages\RestApiClient\Specification;

interface Specification
{
    /**
     * 获得请求的头部信息
     * 
     * @param  string $token     授权码
     * @param  string $requestId 本次请求ID
     * @return array             请求头部的一个列表
     */
    public function getHeaders($token, $requestId = '');

    /**
     * 对请求制作授权码
     * 
     * @param  array $config    配置含accessKey、secretKey
     * @param  string $uri      请求的API地址，不含host信息
     * @param  string $body     请求内容体，如无请求体，则为空。
     * @param  intger $deadline 请求的有效期，为时间戳。
     * @param  string $once     随机码，防止重放攻击，在有效期内该随机码只能出现一次，最长16位
     * @return array            返回含
     */
    public function packToken($config, $uri, $body, $deadline, $once);

    /**
     * 对授权码解包
     * 
     * @param  string $token 授权码
     * @return array         解包后的授权码
     * 
     */
    public function unpackToken($token);

    /**
     * 对请求制作签名，防止请求数据被篡改
     * 
     * @param  array $config    配置含accessKey、secretKey
     * @param  string $url      请求的API地址，不含host信息
     * @param  string $body     请求内容体，如无请求体，则为空。
     * @param  intger $deadline 请求的有效期，为时间戳。
     * @param  string $once     随机码，防止重放攻击，在有效期内该随机码只能出现一次，最长16位
     * @return string           本次请求的签名
     */
    public function signature($config, $uri, $body, $deadline, $once);

    public function serialize($data);

    public function unserialize($data);
}