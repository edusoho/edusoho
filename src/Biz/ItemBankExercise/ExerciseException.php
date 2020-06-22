<?php

namespace Biz\ItemBankExercise;

use AppBundle\Common\Exception\AbstractException;

class ExerciseException extends AbstractException
{
    const EXCEPTION_MODUAL = 69;

    const NOTFOUND_EXERCISE = 4046901;

    const FORBIDDEN_MANAGE_EXERCISE = 4036902;

    const UNPUBLISHED_EXERCISE = 4036903;

    public $messages = [
        4046901 => 'exception.item_bank_exercise.exercise.not_found',
        4036902 => 'exception.item_bank_exercise.exercise.forbidden_manage_exercise',
        4036903 => 'exception.item_bank_exercise.exercise.exercise_not_published',
    ];
}
