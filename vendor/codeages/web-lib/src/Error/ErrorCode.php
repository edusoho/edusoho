<?php

namespace Codeages\Weblib\Error;

class ErrorCode
{
    /**
     * 路由不存在 (http code: 404).
     */
    const NOT_FOUND = 1;

    /**
     * 请求报文格式不正确 (http code: 400)
     * 例如：
     * 1. 请求体非json格式
     * 2. 未设置application/json头部.
     */
    const BAD_REQUEST = 2;

    /**
     * API请求次数已达上限 (http code: 403).
     */
    const TOO_MANY_CALLS = 3;

    /**
     * 请求Credential不正确 (http code: 401)
     * 1. Credential格式不正确
     * 3. Credential签名不正确
     * 4. API对应的AccessKey不存在 (此情况不应反馈到客户端，也应跟情况3一样返回错误信息，以免被猜测攻击).
     */
    const INVALID_CREDENTIAL = 4;

    /**
     * 认证信息已过期 (http code: 401).
     */
    const EXPIRED_CREDENTIAL = 5;

    /**
     * Credential对应的用户被禁止 (http code: 401).
     */
    const BANNED_CREDENTIALS = 6;

    /**
     * 服务内部错误，需联系管理员 (http code: 500).
     */
    const INTERNAL_SERVER_ERROR = 7;

    /**
     * 服务暂时下线，请稍后重试 (http code: 503)
     * 1. 升级维护中
     * 2. 过载保护中
     * 3. 内部服务处理超时.
     */
    const SERVICE_UNAVAILABLE = 8;

    /**
     * 参数缺失、参数不正确 (http code: 422).
     */
    const INVALID_ARGUMENT = 9;

    /**
     * 访问的资源不存在 (http code: 404).
     */
    const RESOURCE_NOT_FOUND = 10;

    /**
     * 未认证
     */
    const UNAUTHORIZED = 11;

    /**
     * 无权访问
     */
    const FORBIDDEN = 12;
}
