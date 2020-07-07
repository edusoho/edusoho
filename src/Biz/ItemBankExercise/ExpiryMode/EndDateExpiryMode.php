<?php

namespace Biz\ItemBankExercise\ExpiryMode;

use AppBundle\Common\TimeMachine;
use Biz\ItemBankExercise\ItemBankExerciseException;

class EndDateExpiryMode extends ExpiryMode
{
    //expiryMode: days, date, end_date, forever
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
        if (self::EXPIRY_MODE_END_DATE == $exercise['expiryMode']) {
            $exercise['expiryStartDate'] = 0;
            $exercise['expiryDays'] = 0;

            if (empty($exercise['expiryEndDate'])) {
                return ItemBankExerciseException::EXPIRYENDDATE_REQUIRED();
            }
            $exercise['expiryEndDate'] = TimeMachine::isTimestamp($exercise['expiryEndDate']) ? $exercise['expiryEndDate'] : strtotime($exercise['expiryEndDate'].' 23:59:59');
        } else {
            return ItemBankExerciseException::EXPIRYMODE_INVALID();
        }

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

    public function getUpdateDeadline($exercise, $member, $setting)
    {
        if ($setting['updateType'] == 'day') {
            $originDeadline = $member['deadline'] > 0 ? $member['deadline'] : time();
            $deadline = 'plus' == $setting['waveType'] ? $originDeadline + $setting['day'] * 24 * 60 * 60 : $originDeadline - $setting['day'] * 24 * 60 * 60;
        } else {
            $deadline = TimeMachine::isTimestamp($setting['deadline']) ? $setting['deadline'] : strtotime($setting['deadline'] . ' 23:59:59');
        }

        return $deadline;
    }
}
