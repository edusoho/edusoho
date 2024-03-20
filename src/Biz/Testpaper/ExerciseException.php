<?php

namespace Biz\Testpaper;

use AppBundle\Common\Exception\AbstractException;

class ExerciseException extends AbstractException
{
    const NOTFOUND_EXERCISE = 4046801;

    const DRAFT_EXERCISE = 4036802;

    const CLOSED_EXERCISE = 4036803;

    const FORBIDDEN_ACCESS_EXERCISE = 4036804;

    const FORBIDDEN_DUPLICATE_COMMIT = 4036805;

    const REVIEWING_EXERCISE = 4036806;

    const NOTFOUND_RESULT = 4046806;

    const EXERCISE_IS_DOING = 4006807;

    const EXERCISE_NOTDO = 4006808;

    const LACK_QUESTION = 4006809;

    public $messages = [
        4046801 => 'exception.exercise.not_found',
        4036802 => 'exception.exercise.draft',
        4036803 => 'exception.exercise.closed',
        4036804 => 'exception.exercise.forbidden_access_exercise',
        4036805 => 'exception.exercise.forbidden_duplicate_commit_exercise',
        4046806 => 'exception.exercise.not_found_result',
        4006807 => 'exception.exercise.exercise_is_doing',
        4006808 => 'exception.exercise.exercise_not_do',
        4006809 => 'exception.exercise.lack_question',
    ];
}
