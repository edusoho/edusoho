<?php

namespace Biz\User\Register;

use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use AppBundle\Common\SimpleValidator;

class EmailRegister extends BaseRegister
{
    protected function validate($registration)
    {
        Parent::validate($registration);

        if (!SimpleValidator::email($registration['email'])) {
            throw new InvalidArgumentException('Invalid Email');
        }

        if (!$this->getUserService()->isEmailAvaliable($registration['email'])) {
            throw new InvalidArgumentException('Email Occupied');
        }
    }
}
