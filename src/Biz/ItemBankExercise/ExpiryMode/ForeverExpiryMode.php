<?php

namespace Biz\ItemBankExercise\ExpiryMode;

use AppBundle\Common\TimeMachine;
use Biz\ItemBankExercise\ItemBankExerciseException;

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

    public function isExpired($exercise)
    {
        return false;
    }

    public function getUpdateDeadline($exercise, $member, $setting)
    {
        return TimeMachine::isTimestamp($setting['deadline']) ? $setting['deadline'] : strtotime($setting['deadline'].' 23:59:59');
    }
}
