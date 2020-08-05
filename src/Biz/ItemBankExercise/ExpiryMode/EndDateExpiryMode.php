<?php

namespace Biz\ItemBankExercise\ExpiryMode;

use AppBundle\Common\TimeMachine;
use Biz\ItemBankExercise\ItemBankExerciseException;

class EndDateExpiryMode extends ExpiryMode
{
    const EXPIRY_MODE_END_DATE = 'end_date';

    public function getDeadline($exercise)
    {
        $deadline = 0;
        if (self::EXPIRY_MODE_END_DATE == $exercise['expiryMode']) {
            $deadline = $exercise['expiryEndDate'];
        }

        return $deadline;
    }

    public function validateExpiryMode($exercise)
    {
        $exercise['expiryStartDate'] = 0;
        $exercise['expiryDays'] = 0;

        if (empty($exercise['expiryEndDate'])) {
            return ItemBankExerciseException::EXPIRYENDDATE_REQUIRED();
        }
        $exercise['expiryEndDate'] = TimeMachine::isTimestamp($exercise['expiryEndDate']) ? $exercise['expiryEndDate'] : strtotime($exercise['expiryEndDate'].' 23:59:59');

        return $exercise;
    }

    public function isExpired($exercise)
    {
        $expiryMode = $exercise['expiryMode'];
        if (self::EXPIRY_MODE_END_DATE == $expiryMode) {
            $isExpired = $exercise['expiryEndDate'] > time() ? false : true;
        } else {
            $isExpired = false;
        }

        return $isExpired;
    }
}
