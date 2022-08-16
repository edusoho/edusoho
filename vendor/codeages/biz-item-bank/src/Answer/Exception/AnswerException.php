<?php

namespace Codeages\Biz\ItemBank\Answer\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class AnswerException extends \Exception implements HttpExceptionInterface
{
    public function getStatusCode()
    {
        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    public function getHeaders()
    {
        return [];
    }
}
