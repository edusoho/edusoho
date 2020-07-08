<?php

namespace Biz\ItemBankExercise\ExpiryMode;

use AppBundle\Common\TimeMachine;
use Biz\ItemBankExercise\ItemBankExerciseException;

class DateExpiryMode extends ExpiryMode
{
    const EXPIRY_MODE_DATE = 'date';

    public function getDeadline($exercise)
    {
        return $exercise['expiryEndDate'];
    }

    public function validateExpiryMode($exercise)
    {
        $exercise['expiryDays'] = 0;
        $exercise['expiryStartDate'] = TimeMachine::isTimestamp($exercise['expiryStartDate']) ? $exercise['expiryStartDate'] : strtotime($exercise['expiryStartDate']);
        $exercise['expiryEndDate'] = TimeMachine::isTimestamp($exercise['expiryEndDate']) ? $exercise['expiryEndDate'] : strtotime($exercise['expiryEndDate'].' 23:59:59');

        if ($exercise['expiryEndDate'] <= $exercise['expiryStartDate']) {
            return ItemBankExerciseException::EXPIRY_DATE_SET_INVALID();
        }

        return $exercise;
    }

    public function isExpired($exercise)
    {
        $expiryMode = $exercise['expiryMode'];
        if (self::EXPIRY_MODE_DATE == $expiryMode) {
            $isExpired = $exercise['expiryStartDate'] <= time() && $exercise['expiryEndDate'] > time() ? false : true;
        } else {
            $isExpired = false;
        }

        return $isExpired;
    }
}
