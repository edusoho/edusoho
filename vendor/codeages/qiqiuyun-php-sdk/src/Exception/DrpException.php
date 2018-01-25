<?php

namespace QiQiuYun\SDK\Exception;

class DrpException extends SDKException
{
    /**
     * 上报的数据格式不合法
     */
    const POST_DATA_INVALID_ARGUMENT = 4108;

    /**
     * 上报数据进入队列失败
     */
    const POST_DATA_PUT_WORKER_ERROR = 4109;

    /**
     * 上报数据的token不合法
     */
    const POST_DATA_TOKEN_INVALID = 4110;

    /**
     * 上报数据的签名不合法
     */
    const POST_DATA_SIGN_INVALID = 4111;
}
