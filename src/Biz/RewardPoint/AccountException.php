<?php

namespace Biz\RewardPoint;

use AppBundle\Common\Exception\AbstractException;

class AccountException extends AbstractException
{
    const EXCEPTION_MODUAL = 57;

    const NOTFOUND_ACCOUNT = 4045701;

    const ALREADY_OPEN = 5005702;

    const USERID_INVALID = 5005703;

    const BALANCE_INSUFFICIENT = 5005704;

    const ERROR_PAY_PASSWORD = 5005705;

    public $messages = array(
        4045701 => 'exception.reward_point.account.not_found',
        5005702 => 'exception.reward_point.account.already_open',
        5005703 => 'exception.reward_point.account.userid_invalid',
        5005704 => 'exception.reward_point.account.balance_insufficient',
        5005705 => 'exception.reward_point.account.error_pay_password',
    );
}
