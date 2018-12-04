<?php

namespace ApiBundle\Api\Exception;

class ErrorCode
{
    const API_NOT_FOUND = 1;

    const BAD_REQUEST = 2;

    const API_TOO_MANY_CALLS = 3;

    const INVALID_CREDENTIAL = 4;

    const EXPIRED_CREDENTIAL = 5;

    const BANNED_CREDENTIAL = 6;

    const INTERNAL_SERVER_ERROR = 7;

    const SERVICE_UNAVAILABLE = 8;

    const INVALID_ARGUMENT = 9;

    const RESOURCE_NOT_FOUND = 10;

    const UNAUTHORIZED = 11;

    const METHOD_NOT_ALLOWED = 12;
}
