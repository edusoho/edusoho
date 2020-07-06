<?php

namespace Biz\ItemBankExercise\ExpiryMode;

use AppBundle\Common\TimeMachine;
use Biz\ItemBankExercise\ItemBankExerciseException;
use Biz\ItemBankExercise\Service\ExerciseService;

class ExerciseExpiryMode extends ExpiryMode
{
    //expiryMode: days, date, end_date, forever
    const EXPIRY_MODE_DAYS = 'days';
    const EXPIRY_MODE_DATE = 'date';
    const EXPIRY_MODE_END_DATE = 'end_date';
    const EXPIRY_MODE_FOREVER = 'forever';

    public static function getDeadline($exercise)
    {
        $deadline = 0;
        if (self::EXPIRY_MODE_DAYS == $exercise['expiryMode'] && $exercise['expiryDays'] > 0) {
            $endTime = strtotime(date('Y-m-d', time()).' 23:59:59'); //系统当前时间
            $deadline = $exercise['expiryDays'] * 24 * 60 * 60 + $endTime;
        } elseif (self::EXPIRY_MODE_DATE == $exercise['expiryMode'] || 'end_date' == $exercise['expiryMode']) {
            $deadline = $exercise['expiryEndDate'];
        }

        return $deadline;
    }

    public function validateExpiryMode($exercise)
    {
        if (empty($exercise['expiryMode'])) {
            return $exercise;
        }
        if (self::EXPIRY_MODE_DAYS === $exercise['expiryMode']) {
            $exercise['expiryStartDate'] = 0;
            $exercise['expiryEndDate'] = 0;

            if (empty($exercise['expiryDays'])) {
                return ItemBankExerciseException::EXPIRYDAYS_REQUIRED();
            }
            if ($exercise['expiryDays'] > ExerciseService::MAX_EXPIRY_DAY) {
                return ItemBankExerciseException::EXPIRYDAYS_INVALID();
            }
        } elseif (self::EXPIRY_MODE_END_DATE == $exercise['expiryMode']) {
            $exercise['expiryStartDate'] = 0;
            $exercise['expiryDays'] = 0;

            if (empty($exercise['expiryEndDate'])) {
                return ItemBankExerciseException::EXPIRYENDDATE_REQUIRED();
            }
            $exercise['expiryEndDate'] = TimeMachine::isTimestamp($exercise['expiryEndDate']) ? $exercise['expiryEndDate'] : strtotime($exercise['expiryEndDate'].' 23:59:59');
        } elseif (self::EXPIRY_MODE_DATE === $exercise['expiryMode']) {
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
        } elseif (self::EXPIRY_MODE_FOREVER == $exercise['expiryMode']) {
            $exercise['expiryStartDate'] = 0;
            $exercise['expiryEndDate'] = 0;
            $exercise['expiryDays'] = 0;
        } else {
            return ItemBankExerciseException::EXPIRYMODE_INVALID();
        }

        return $exercise;
    }

    public static function canUpdateDeadline($expiryMode)
    {
        if (self::EXPIRY_MODE_FOREVER == $expiryMode) {
            return false;
        }

        return true;
    }

    public static function filterUpdateExpiryInfo($exercise, $fields)
    {
        if (in_array($exercise['status'], ['published', 'closed'])) {
            //发布或者关闭，不允许修改模式，但是允许修改时间
            unset($fields['expiryMode']);
            if ('published' == $exercise['status']) {
                //发布后，不允许修改时间
                unset($fields['expiryDays']);
                unset($fields['expiryStartDate']);
                unset($fields['expiryEndDate']);
            }
        }

        return $fields;
    }

    public static function getDeadlineByWaveType($originDeadline, $waveType, $day)
    {
        $originDeadline = $originDeadline > 0 ? $originDeadline : time();
        $deadline = 'plus' == $waveType ? $originDeadline + $day * 24 * 60 * 60 : $originDeadline - $day * 24 * 60 * 60;

        return $deadline;
    }

    public static function isExpired($exercise)
    {
        $expiryMode = $exercise['expiryMode'];
        $now = strtotime();
        if (self::EXPIRY_MODE_DAYS == $expiryMode) {
            $isExpired = true;
        } elseif (self::EXPIRY_MODE_DATE == $expiryMode) {
            $isExpired = $exercise['expiryStartDate'] <= $now && $exercise['expiryEndDate'] > $now;
        } elseif (self::EXPIRY_MODE_END_DATE == $expiryMode) {
            $isExpired = $exercise['expiryEndDate'] > $now;
        } elseif (self::EXPIRY_MODE_FOREVER == $expiryMode) {
            $isExpired = true;
        } else {
            $isExpired = false;
        }

        return $isExpired;
    }
}
