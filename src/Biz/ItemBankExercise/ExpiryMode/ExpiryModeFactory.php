<?php

namespace Biz\ItemBankExercise\ExpiryMode;

class ExpiryModeFactory
{
    public static function create($expiryMode)
    {
        $export = self::expiryModeMap($expiryMode);

        return new $export();
    }

    private function expiryModeMap($name)
    {
        $map = [
            'days' => 'Biz\ItemBankExercise\ExpiryMode\DaysExpiryMode',
            'date' => 'Biz\ItemBankExercise\ExpiryMode\DateExpiryMode',
            'end_date' => 'Biz\ItemBankExercise\ExpiryMode\EndDateExpiryMode',
            'forever' => 'Biz\ItemBankExercise\ExpiryMode\ForeverExpiryMode',
        ];

        if (empty($map[$name])) {
            $map[$name] = 'Biz\ItemBankExercise\ExpiryMode\ForeverExpiryMode';
        }

        return $map[$name];
    }
}
