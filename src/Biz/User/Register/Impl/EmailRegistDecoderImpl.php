<?php

namespace Biz\User\Register\Impl;

use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use AppBundle\Common\SimpleValidator;

class EmailRegistDecoderImpl extends RegistDecoder
{
    protected function validateBeforeSave($registration, $type)
    {
        if (!SimpleValidator::email($registration['email'])) {
            throw new InvalidArgumentException('Invalid Email');
        }

        if (!$this->getUserService()->isEmailAvaliable($registration['email'])) {
            throw new InvalidArgumentException('Email Occupied');
        }
    }
}
