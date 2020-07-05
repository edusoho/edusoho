<?php

namespace Biz\ItemBankExercise;

use AppBundle\Common\Exception\AbstractException;

class ItemBankExerciseException extends AbstractException
{
    const EXCEPTION_MODULE = 75;

    const FORBIDDEN_LEARN = 4037501;

    const CHAPTER_ANSWER_IS_DOING = 5007502;

    const NOTFOUND_EXERCISE = 4047503;

    const FORBIDDEN_MANAGE_EXERCISE = 4037504;

    const UNPUBLISHED_EXERCISE = 4037505;

    const EXPIRYDAYS_REQUIRED = 5007506;

    const EXPIRYDAYS_INVALID = 5007507;

    const EXPIRYENDDATE_REQUIRED = 5007508;

    const EXPIRYSTARTDATE_REQUIRED = 5007509;

    const EXPIRY_DATE_SET_INVALID = 5007510;

    const EXPIRYMODE_INVALID = 5007511;

    const CHAPTER_EXERCISE_CLOSED = 5007512;

    const ASSESSMENT_EXERCISE_CLOSED = 5007513;

    const ASSESSMENT_ANSWER_IS_DOING = 5007514;

    const ASSESSMENT_EXCEED = 5007515;

    const NOTFOUND_MODULE = 4047516;

    const ASSESSMENT_EXERCISE_EXIST = 5007517;

    public $messages = [
        4037501 => 'exception.item_bank_exercise.forbidden_learn',
        5007502 => 'exception.item_bank_exercise.chapter_answer_is_doing',
        4047503 => 'exception.item_bank_exercise.exercise.not_found',
        4037504 => 'exception.item_bank_exercise.exercise.forbidden_manage_exercise',
        4037505 => 'exception.item_bank_exercise.exercise.exercise_not_published',
        5007506 => 'exception.item_bank_exercise.expirydays_required',
        5007507 => 'exception.item_bank_exercise.expirydays_invalid',
        5007508 => 'exception.item_bank_exercise.expiryenddate_required',
        5007509 => 'exception.item_bank_exercise.expirystartdate_required',
        5007510 => 'exception.item_bank_exercise.expirydate_end_later_than_start',
        5007511 => 'exception.item_bank_exercise.expirymode_invalid',
        5007512 => 'exception.item_bank_exercise.chapter_exercise_closed',
        5007513 => 'exception.item_bank_exercise.assessment_exercise_closed',
        5007514 => 'exception.item_bank_exercise.assessment_answer_is_doing',
        5007515 => 'exception.item_bank_exercise.module.exceeded_number',
        4047516 => 'exception.item_bank_exercise.exercise.module_not_found',
        5007517 => 'exception.item_bank_exercise.assessment_exercise_exist',
    ];
}
