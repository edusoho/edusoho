<?php

// src/Service/ResponseBuilder.php

namespace AgentBundle\Common;

use Symfony\Component\HttpFoundation\JsonResponse;

class ResponseBuilder
{
    // 成功响应（保留数据字段）
    public static function success($data = null): JsonResponse
    {
        return new JsonResponse([
            'status' => 'OK',
            'data' => $data,
        ], 200);
    }

    // 错误响应（支持字符串错误码）
    public static function error(
        string $errorCode,    // 业务错误码（字符串类型）
        string $errorMessage, // 错误描述
        int $httpCode = 400   // HTTP状态码
    ): JsonResponse {
        return new JsonResponse([
            'status' => $errorCode,
            'error' => $errorMessage,
        ], $httpCode);
    }
}
