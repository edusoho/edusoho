<?php

namespace ESLive\SDK;

use RuntimeException;

class SDKException extends RuntimeException
{
    private $errorCode;

    protected $traceId;

    public function __construct(string $message, string $errorCode, string $traceId)
    {
        parent::__construct($message);
        $this->errorCode = $errorCode;
        $this->traceId = $traceId;
    }

    public function getErrorCode(): string {
        return $this->errorCode;
    }

    public function getTraceId(): string {
        return $this->traceId;
    }
}