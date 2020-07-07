<?php

namespace Biz\ItemBankExercise\ExpiryMode;

use AppBundle\Common\TimeMachine;
use Biz\ItemBankExercise\ItemBankExerciseException;
use Biz\ItemBankExercise\Service\ExerciseService;

class ForeverExpiryMode extends ExpiryMode
{
    const EXPIRY_MODE_FOREVER = 'forever';

    public function getDeadline($exercise)
    {
        return 0;
    }

    public function validateExpiryMode($exercise)
    {
        if (self::EXPIRY_MODE_FOREVER == $exercise['expiryMode']) {
            $exercise['expiryStartDate'] = 0;
            $exercise['expiryEndDate'] = 0;
            $exercise['expiryDays'] = 0;
        } else {
            return ItemBankExerciseException::EXPIRYMODE_INVALID();
        }

        return $exercise;
    }

    public function canUpdateDeadline($expiryMode)
    {
        if (self::EXPIRY_MODE_FOREVER == $expiryMode) {
            return false;
        }

        return true;
    }

    public static function isExpired($exercise)
    {
        return false;
    }
}
