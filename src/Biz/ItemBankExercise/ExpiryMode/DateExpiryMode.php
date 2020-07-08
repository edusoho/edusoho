<?php

namespace Biz\ItemBankExercise\ExpiryMode;

use AppBundle\Common\TimeMachine;
use Biz\ItemBankExercise\ItemBankExerciseException;

class DateExpiryMode extends ExpiryMode
{
    const EXPIRY_MODE_DATE = 'date';

    public function getDeadline($exercise)
    {
        $deadline = 0;
        if (self::EXPIRY_MODE_DATE == $exercise['expiryMode']) {
            $deadline = $exercise['expiryEndDate'];
        }

        return $deadline;
    }

    public function validateExpiryMode($exercise)
    {
        if (self::EXPIRY_MODE_DATE === $exercise['expiryMode']) {
            $exercise['expiryDays'] = 0;
            if (isset($exercise['expiryStartDate'])) {
                $exercise['expiryStartDate'] = TimeMachine::isTimestamp($exercise['expiryStartDate']) ? $exercise['expiryStartDate'] : strtotime($exercise['expiryStartDate']);
            } else {
                return ItemBankExerciseException::EXPIRYSTARTDATE_REQUIRED();
            }
            if (empty($exercise['expiryEndDate'])) {
                return ItemBankExerciseException::EXPIRYENDDATE_REQUIRED();
            } else {
                $exercise['expiryEndDate'] = TimeMachine::isTimestamp($exercise['expiryEndDate']) ? $exercise['expiryEndDate'] : strtotime($exercise['expiryEndDate'].' 23:59:59');
            }
            if ($exercise['expiryEndDate'] <= $exercise['expiryStartDate']) {
                return ItemBankExerciseException::EXPIRY_DATE_SET_INVALID();
            }
        } else {
            return ItemBankExerciseException::EXPIRYMODE_INVALID();
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
