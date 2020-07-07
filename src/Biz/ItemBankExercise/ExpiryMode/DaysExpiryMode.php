<?php

namespace Biz\ItemBankExercise\ExpiryMode;

use AppBundle\Common\TimeMachine;
use Biz\ItemBankExercise\ItemBankExerciseException;
use Biz\ItemBankExercise\Service\ExerciseService;

class DaysExpiryMode extends ExpiryMode
{
    //expiryMode: days, date, end_date, forever
    const EXPIRY_MODE_DAYS = 'days';

    public function getDeadline($exercise)
    {
        $deadline = 0;
        if (self::EXPIRY_MODE_DAYS == $exercise['expiryMode'] && $exercise['expiryDays'] > 0) {
            $endTime = strtotime(date('Y-m-d', time()).' 23:59:59'); //系统当前时间
            $deadline = $exercise['expiryDays'] * 24 * 60 * 60 + $endTime;
        }

        return $deadline;
    }

    public function validateExpiryMode($exercise)
    {
        if (self::EXPIRY_MODE_DAYS === $exercise['expiryMode']) {
            $exercise['expiryStartDate'] = 0;
            $exercise['expiryEndDate'] = 0;

            if (empty($exercise['expiryDays'])) {
                return ItemBankExerciseException::EXPIRYDAYS_REQUIRED();
            }
            if ($exercise['expiryDays'] > ExerciseService::MAX_EXPIRY_DAY) {
                return ItemBankExerciseException::EXPIRYDAYS_INVALID();
            }
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
        if ($setting['updateType'] == 'day') {
            $originDeadline = $member['deadline'] > 0 ? $member['deadline'] : time();
            $deadline = 'plus' == $setting['waveType'] ? $originDeadline + $setting['day'] * 24 * 60 * 60 : $originDeadline - $setting['day'] * 24 * 60 * 60;
        } else {
            $deadline = TimeMachine::isTimestamp($setting['deadline']) ? $setting['deadline'] : strtotime($setting['deadline'] . ' 23:59:59');
        }

        return $deadline;
    }
}
