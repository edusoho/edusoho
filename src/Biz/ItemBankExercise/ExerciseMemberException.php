<?php

namespace Biz\ItemBankExercise;

use AppBundle\Common\Exception\AbstractException;

class ExerciseMemberException extends AbstractException
{
    const EXCEPTION_MODUAL = 70;

    const DUPLICATE_MEMBER = 4037001;

    const NOTFOUND_MEMBER = 4047002;

    public $messages = array(
        4037001 => 'exception.item_bank_exercise.member.duplicate_member',
        4047002 => 'exception.item_bank_exercise.member.not_found',
    );
}
