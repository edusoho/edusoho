<?php

namespace ApiBundle\Api\Exception;

 class BadRequestException extends ApiException
 {
     const HTTP_CODE = 401;

     const TYPE = 'BAD_REQUEST';

     public function __construct($message = 'BAD_REQUEST', $code = 2, \Exception $previous = null)
     {
         parent::__construct($message, $code, $previous);
     }
 }