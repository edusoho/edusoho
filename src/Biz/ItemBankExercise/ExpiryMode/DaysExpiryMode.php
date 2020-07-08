<?php

namespace Biz\ItemBankExercise\ExpiryMode;

use Biz\ItemBankExercise\ItemBankExerciseException;
use Biz\ItemBankExercise\Service\ExerciseService;

class DaysExpiryMode extends ExpiryMode
{
    const EXPIRY_MODE_DAYS = 'days';

    public function getDeadline($exercise)
    {
        $deadline = 0;
        if (self::EXPIRY_MODE_DAYS == $exercise['expiryMode'] && $exercise['expiryDays'] > 0) {
            $endTime = strtotime(date('Y-m-d', time()).' 23:59:59');
            $deadline = $exercise['expiryDays'] * 24 * 60 * 60 + $endTime;
        }

        return $deadline;
    }

    public function validateExpiryMode($exercise)
    {
        $exercise['expiryStartDate'] = 0;
        $exercise['expiryEndDate'] = 0;

        if (empty($exercise['expiryDays'])) {
            return ItemBankExerciseException::EXPIRYDAYS_REQUIRED();
        }
        if ($exercise['expiryDays'] > ExerciseService::MAX_EXPIRY_DAY) {
            return ItemBankExerciseException::EXPIRYDAYS_INVALID();
        }

        return $exercise;
    }

    public function isExpired($exercise)
    {
        return false;
    }
}
