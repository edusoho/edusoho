<?php

namespace Biz\Role;

use AppBundle\Common\Exception\AbstractException;

class RoleException extends AbstractException
{
    const EXCEPTION_MODUAL = 49;

    const FORBIDDEN_MODIFY = 4034901;

    public $messages = array(
        4034901 => 'exception.role.forbidden_modify',
    );
}
