<?php

namespace Biz\ItemBankExercise;

use AppBundle\Common\Exception\AbstractException;

class ItemBankExerciseException extends AbstractException
{
    const EXCEPTION_MODULE = 73;

    const FORBIDDEN_LEARN = 4037301;

    const CANNOT_START_CHAPTER_ANSWER = 4037302;

    public $messages = [
        4037301 => 'exception.item_bank_exercise.forbidden_learn',
        4037302 => 'exception.item_bank_exercise.cannot_start_chapter_answer',
    ];
}
