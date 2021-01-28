<?php

namespace ApiBundle\Api\Util;

use ApiBundle\Api\Exception\ErrorCode;
use AppBundle\Common\ExceptionPrintingToolkit;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionUtil
{
    public static function getErrorAndHttpCodeFromException(\Exception $exception, $isDebug)
    {
        $error = array();
        if ($exception instanceof HttpExceptionInterface) {
            $error['message'] = $exception->getMessage();
            $error['code'] = $exception->getCode();
            $httpCode = $exception->getStatusCode();
        } else {
            $error['message'] = 'Internal server error';
            $error['code'] = $exception->getCode() ?: ErrorCode::INTERNAL_SERVER_ERROR;
            $httpCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        if ($isDebug) {
            $error['trace'] = ExceptionPrintingToolkit::printTraceAsArray($exception);
        }

        return array($error, $httpCode);
    }
}
