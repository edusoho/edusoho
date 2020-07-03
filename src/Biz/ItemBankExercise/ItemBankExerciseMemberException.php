<?php

namespace Biz\ItemBankExercise;

use AppBundle\Common\Exception\AbstractException;

class ItemBankExerciseMemberException extends AbstractException
{
    const EXCEPTION_MODULE = 76;

    const DUPLICATE_MEMBER = 4037601;

    const NOTFOUND_MEMBER = 4047602;

    public $messages = [
        4037601 => 'exception.item_bank_exercise.member.duplicate_member',
        4047602 => 'exception.item_bank_exercise.member.not_found',
    ];
}
