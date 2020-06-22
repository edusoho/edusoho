<?php

namespace Biz\ItemBankExercise;

use AppBundle\Common\Exception\AbstractException;

class ItemBankExerciseMemberException extends AbstractException
{
    const EXCEPTION_MODULE = 74;

    const DUPLICATE_MEMBER = 4037401;

    const NOTFOUND_MEMBER = 4047402;

    public $messages = [
        4037401 => 'exception.item_bank_exercise.member.duplicate_member',
        4047402 => 'exception.item_bank_exercise.member.not_found',
    ];
}
