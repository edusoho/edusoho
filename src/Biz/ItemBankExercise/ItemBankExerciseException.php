<?php

namespace Biz\ItemBankExercise;

use AppBundle\Common\Exception\AbstractException;

class ItemBankExerciseException extends AbstractException
{
    const EXCEPTION_MODULE = 73;

    const FORBIDDEN_LEARN = 4037301;

    const CHAPTER_ANSWER_IS_DOING = 4037302;

    const NOTFOUND_EXERCISE = 4047303;

    const FORBIDDEN_MANAGE_EXERCISE = 4037304;

    const UNPUBLISHED_EXERCISE = 4037305;

    const NOTFOUND_MODULE = 4047306;

    const CHAPTER_EXERCISE_CLOSED = 5007312;

    const ASSESSMENT_EXERCISE_CLOSED = 5007313;

    const ASSESSMENT_ANSWER_IS_DOING = 5007314;

    public $messages = [
        4037301 => 'exception.item_bank_exercise.forbidden_learn',
        4037302 => 'exception.item_bank_exercise.chapter_answer_is_doing',
        4047303 => 'exception.item_bank_exercise.exercise.not_found',
        4037304 => 'exception.item_bank_exercise.exercise.forbidden_manage_exercise',
        4037305 => 'exception.item_bank_exercise.exercise.exercise_not_published',
        4047306 => 'exception.item_bank_exercise.exercise.module_not_found',
        5007312 => 'exception.item_bank_exercise.chapter_exercise_closed',
        5007313 => 'exception.item_bank_exercise.chapter_exercise_closed',
        5007314 => 'exception.item_bank_exercise.assessment_answer_is_doing',
    ];
}
