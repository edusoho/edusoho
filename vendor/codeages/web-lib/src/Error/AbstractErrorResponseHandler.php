<?php

namespace Codeages\Weblib\Error;

use Codeages\Weblib\Auth\AuthException;
use Monolog\Logger;
use Monolog\Processor\WebProcessor;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException as ServiceInvalidArgumentException;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;
use Codeages\Biz\Framework\Service\Exception\ServiceException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class AbstractErrorResponseHandler
{
    protected $debug;

    /**
     * @var Logger
     */
    protected $logger;

    public function __construct($debug, Logger $logger)
    {
        $this->debug = $debug;
        $this->logger = $logger;
    }

    abstract public function handle(\Exception $e);

    public function getError(\Exception $e)
    {
        if ($e instanceof ResourceNotFoundException || $e instanceof NotFoundException) {
            $error = ['code' => ErrorCode::RESOURCE_NOT_FOUND, 'message' => $e->getMessage() ?: 'Resource Not Found.'];
            $statusCode = 404;
        } elseif ($e instanceof NotFoundHttpException) {
            $error = ['code' => ErrorCode::NOT_FOUND, 'message' => $e->getMessage() ?: 'Not Found.'];
            $statusCode = 404;
        } elseif ((($e instanceof AuthException) || ($e instanceof ResourceException) || ($e instanceof ServiceException)) && ($e->getCode() > 0)) {
            $error = ['code' => $e->getCode(), 'message' => $e->getMessage()];
            $statusCode = 400;
        } elseif ($e instanceof ServiceInvalidArgumentException || $e instanceof \InvalidArgumentException) {
            $error = ['code' => ErrorCode::INVALID_ARGUMENT, 'message' => $e->getMessage()];
            $statusCode = 422;
        } else {
            $error = ['code' => ErrorCode::SERVICE_UNAVAILABLE, 'message' => 'Service unavailable.'];
            $statusCode = 500;
        }
        $error['trace_id'] = time().'_'.substr(hash('md5', uniqid('', true)), 0, 10);

        if ($this->debug) {
            $error['detail'] = (string) $e;
        }

        if ($this->logger) {
            $logger = $this->logger;
            $logger->pushProcessor(new WebProcessor());
            $logger->pushProcessor(function ($record) use ($error) {
                $record['extra']['trace_id'] = $error['trace_id'];
                $record['extra']['http_body'] = file_get_contents('php://input');

                return $record;
            });
            $logger->error((string) $e);
        }

        return [
            'error' => $error,
            'http_code' => $statusCode,
        ];
    }
}