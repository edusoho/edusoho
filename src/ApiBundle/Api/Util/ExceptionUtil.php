<?php

namespace ApiBundle\Api\Util;

use ApiBundle\Api\Exception\ErrorCode;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
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
        } else{
            $error['message'] = 'Internal server error';
            $error['code'] = $exception->getCode() ? : ErrorCode::INTERNAL_SERVER_ERROR;
            $httpCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        if ($isDebug) {

            if (!$exception instanceof FlattenException) {
                $exception = FlattenException::create($exception);
            }

            $error['previous'] = array();

            $flags = PHP_VERSION_ID >= 50400 ? ENT_QUOTES | ENT_SUBSTITUTE : ENT_QUOTES;

            $count = count($exception->getAllPrevious());
            $total = $count + 1;
            foreach ($exception->toArray() as $position => $e) {
                $previous = array();

                $ind = $count - $position + 1;

                $previous['message'] = "{$ind}/{$total} {$e['class']}: {$e['message']}";
                $previous['trace'] = array();

                foreach ($e['trace'] as $position => $trace) {
                    $content = sprintf('%s. ', $position+1);
                    if ($trace['function']) {
                        $content .= sprintf('at %s%s%s(%s)', $trace['class'], $trace['type'], $trace['function'], '...args...');
                    }
                    if (isset($trace['file']) && isset($trace['line'])) {
                        $content .= sprintf(' in %s line %d', htmlspecialchars($trace['file'], $flags, 'UTF-8'), $trace['line']);
                    }

                    $previous['trace'][] = $content;
                }

                $error['previous'][] = $previous;
            }
        }

        return array($error, $httpCode);
    }
}