<?php

namespace Biz\ItemBankExercise;

use AppBundle\Common\Exception\AbstractException;

class ItemBankExerciseException extends AbstractException
{
    const FORBIDDEN_LEARN = 4039001;

    const CANNOT_START_CHAPTER_ANSWER = 4039002;

    public $messages = [
        4039001 => 'exception.item_bank_exercise.forbidden_learn',
        4039002 => 'exception.item_bank_exercise.cannot_start_chapter_answer',
    ];
}
