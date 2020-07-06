<?php


namespace Biz\ItemBankExercise\ExpiryMode;


abstract class ExpiryMode
{
    abstract function validateExpiryMode($exercise);

    abstract static function getDeadline($exercise);

    abstract static function getDeadlineByWaveType($originDeadline, $waveType, $day);

    abstract static function canUpdateDeadline($expiryMode);

    abstract static function filterUpdateExpiryInfo($exercise, $fields);
}