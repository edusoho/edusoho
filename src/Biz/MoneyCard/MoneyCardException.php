<?php

namespace Biz\MoneyCard;

use AppBundle\Common\Exception\AbstractException;

class MoneyCardException extends AbstractException
{
    const EXCEPTION_MODULE = 56;

    const MONEY_INVALID = 5005601;

    const COIN_INVALID = 5005602;

    const CARDLENGTH_INVALID = 5005603;

    const NUMBER_INVALID = 5005604;

    const NOTFOUND_MONEYCARD = 4045605;

    const LOCK_USED_CARD = 5005606;

    const NOTFOUND_BATCH = 4045607;

    const DUPLICATE_CARD = 5005608;

    const BATCH_STATUS_EQUAL_INVALID = 5005609;

    const UNLOCK_NOT_INVALID_CARD = 5005610;

    const PASSWORD_INVALID = 5005611;

    const BATCH_TOKEN_INVALID = 5005612;

    public $messages = [
        5005601 => 'exception.money_card.money_invalid',
        5005602 => 'exception.money_card.coin_invalid',
        5005603 => 'exception.money_card.cardlength_invalid',
        5005604 => 'exception.money_card.number_invalid',
        4045605 => 'exception.money_card.not_found',
        5005606 => 'exception.money_card.lock_used_card',
        4045607 => 'exception.money_card.not_found_batch',
        5005608 => 'exception.money_card.duplicate_card',
        5005609 => 'exception.money_card.batch_status_equal_invalid',
        5005610 => 'exception.money_card.unlock_not_invalid_card',
        5005611 => 'exception.money_card.password_invalid',
        5005612 => 'exception.money_card.batch_token_invalid',
    ];
}
