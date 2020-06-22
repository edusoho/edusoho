<?php

namespace Biz\ItemBankExercise;

use AppBundle\Common\Exception\AbstractException;

class ItemBankExerciseException extends AbstractException
{
    const EXCEPTION_MODULE = 90;

    const FORBIDDEN_LEARN = 4039001;

    const CANNOT_START_CHAPTER_ANSWER = 4039002;

    const NOTFOUND_EXERCISE = 4049003;

    const FORBIDDEN_MANAGE_EXERCISE = 4039004;

    const UNPUBLISHED_EXERCISE = 4039005;

    public $messages = [
        4039001 => 'exception.item_bank_exercise.forbidden_learn',
        4039002 => 'exception.item_bank_exercise.cannot_start_chapter_answer',
        4049003 => 'exception.item_bank_exercise.exercise.not_found',
        4039004 => 'exception.item_bank_exercise.exercise.forbidden_manage_exercise',
        4039005 => 'exception.item_bank_exercise.exercise.exercise_not_published',
    ];
}
