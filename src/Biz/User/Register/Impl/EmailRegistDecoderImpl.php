<?php

namespace Biz\User\Register\Impl;

use Biz\User\UserException;
use AppBundle\Common\SimpleValidator;

class EmailRegistDecoderImpl extends RegistDecoder
{
    protected function validateBeforeSave($registration)
    {
        if (empty($registration['email']) || !SimpleValidator::email($registration['email'])) {
            throw UserException::EMAIL_INVALID();
        }

        if (!$this->getUserService()->isEmailAvaliable($registration['email'])) {
            throw UserException::EMAIL_EXISTED();
        }
    }
}
