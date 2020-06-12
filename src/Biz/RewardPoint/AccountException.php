<?php

namespace Biz\RewardPoint;

use AppBundle\Common\Exception\AbstractException;

class AccountException extends AbstractException
{
    const EXCEPTION_MODULE = 57;

    const NOTFOUND_ACCOUNT = 4045701;

    const ALREADY_OPEN = 5005702;

    const USERID_INVALID = 5005703;

    const BALANCE_INSUFFICIENT = 5005704;

    const ERROR_PAY_PASSWORD = 5005705;

    const PAY_PASSWORD_EXISTED = 5005706;

    const NOTFOUND_PAY_PASSWORD = 5005707;

    const ERROR_PAY_PASSWORD_FORMAT = 5005708;

    public $messages = [
        4045701 => 'exception.reward_point.account.not_found',
        5005702 => 'exception.reward_point.account.already_open',
        5005703 => 'exception.reward_point.account.userid_invalid',
        5005704 => 'exception.reward_point.account.balance_insufficient',
        5005705 => 'exception.reward_point.account.error_pay_password',
        5005706 => 'exception.reward_point.account.pay_password_existed',
        5005707 => 'exception.reward_point.account.not_found_pay_password',
        5005708 => 'exception.reward_point.account.error_pay_password_format',
    ];
}
