<?php

namespace Biz\ItemBankExercise\ExpiryMode;

abstract class ExpiryMode
{
    abstract public function validateExpiryMode($exercise);

    abstract public function getDeadline($exercise);

    abstract public function canUpdateDeadline($expiryMode);

    abstract public static function isExpired($exercise);

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
}
