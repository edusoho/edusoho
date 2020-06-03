<?php

namespace Biz\Role;

use AppBundle\Common\Exception\AbstractException;

class RoleException extends AbstractException
{
    const EXCEPTION_MODULE = 49;

    const FORBIDDEN_MODIFY = 4034901;

    const CODE_NOT_ALLL_DIGITAL = 4034902;

    public $messages = [
        4034901 => 'exception.role.forbidden_modify',
        4034902 => 'exception.role.code_not_all_digital',
    ];
}
