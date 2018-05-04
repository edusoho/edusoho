<?php

namespace QiQiuYun\SDK;

/**
 * 系统通用错误码
 *
 * 约定100以内的错误码为系统通用错误码，业务错误码请设置为100以上。
 */
class ErrorCode
{
    /**
     * 接口、资源不存在 (http code: 404)
     */
    const NOT_FOUND = 1;

    /**
     * 请求报文格式不正确 (http code: 400)
     * 例如：
     * 1. 请求体非 JSON 格式
     * 2. 未设置 application/json 头部
     */
    const BAD_REQUEST = 2;

    /**
     * API请求次数已达上限 (http code: 429)
     */
    const TOO_MANY_CALLS = 3;

    /**
     * 请求认证非法 (http code: 401)
     *
     * 1. 认证信息格式不正确
     * 2. 认证签名不正确
     * 3. 认证对应的用户不存在
     * 4. 认证信息已过期
     */
    const INVALID_AUTHENTICATION = 4;

    /**
     * 服务内部错误，需联系管理员 (http code: 500)
     */
    const INTERNAL_SERVER_ERROR = 5;

    /**
     * 服务暂时下线，请稍后重试 (http code: 503)
     *
     * 1. 升级维护中
     * 2. 过载保护中
     */
    const SERVICE_UNAVAILABLE = 6;

    /**
     * 权限不足或帐号被禁用，无权访问 (http code: 403)
     */
    const ACCESS_DENIED = 7;

    /**
     * 参数缺失、参数不正确 (http code: 400)
     */
    const INVALID_ARGUMENT = 8;

    /**
     * 网关已收到请求，但处理超时，可重试 ( http code: 504)
     */
    const TIMEOUT = 9;
}
