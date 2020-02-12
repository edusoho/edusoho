<?php

namespace Biz\DestroyAccount;

use AppBundle\Common\Exception\AbstractException;

class DestroyAccountException extends AbstractException
{
    const EXCEPTION_MODUAL = 67;

    const REASON_TOO_LONG = 5006701;

    public $messages = array(
        40455006701301 => 'exception.destroy_account.reason_too_long',
    );
}