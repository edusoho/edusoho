<?php

namespace Codeages\Biz\ItemBank\Answer\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class AnswerSceneException extends \Exception implements HttpExceptionInterface
{
    public function getStatusCode()
    {
        return Response::HTTP_BAD_REQUEST;
    }

    public function getHeaders()
    {
        return [];
    }
}
