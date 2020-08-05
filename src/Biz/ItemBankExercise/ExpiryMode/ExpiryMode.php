<?php

namespace Biz\ItemBankExercise\ExpiryMode;

use AppBundle\Common\TimeMachine;

abstract class ExpiryMode
{
    abstract public function validateExpiryMode($exercise);

    abstract public function getDeadline($exercise);

    abstract public function isExpired($exercise);

    public function getUpdateDeadline($member, $setting)
    {
        if ('day' == $setting['updateType']) {
            $originDeadline = $member['deadline'] > 0 ? $member['deadline'] : time();
            $deadline = 'plus' == $setting['waveType'] ? $originDeadline + $setting['day'] * 24 * 60 * 60 : $originDeadline - $setting['day'] * 24 * 60 * 60;
        } else {
            $deadline = TimeMachine::isTimestamp($setting['deadline']) ? $setting['deadline'] : strtotime($setting['deadline'].' 23:59:59');
        }

        return $deadline;
    }
}
