<?php

namespace AppBundle\Common;

class PasswordGenerateToolkit
{
    private static $levels = ['low', 'middle', 'high'];

    public static function create($passwordLevel)
    {
        if (!in_array($passwordLevel, self::$levels)) {
            throw new \Exception('Password level not allowed');
        }

        return self::generate(self::getLength($passwordLevel), self::getRule($passwordLevel));
    }

    private static function getRule($passwordLevel)
    {
        $defaultRule = ['number' => 'require'];

        switch ($passwordLevel) {
            case 'middle':
                $rule = ['letter' => 'lower', 'number' => 'require'];
                break;
            case 'high':
                $rule = ['letter' => 'lowerAndUpper', 'number' => 'require', 'special' => 'require'];
                break;
            default:
                $rule = $defaultRule;
                break;
        }

        return $rule;
    }

    private static function getLength($passwordLevel)
    {
        $defaultLength = 5;

        switch ($passwordLevel) {
            case 'low':
                $length = mt_rand(5, 20);
                break;
            case 'middle':
                $length = mt_rand(8, 20);
                break;
            case 'high':
                $length = mt_rand(8, 32);
                break;
            default:
                $length = $defaultLength;
                break;
        }

        return $length;
    }

    private static function generate($length, $rule = [])
    {
        $pool = '';
        $force_pool = '';

        if (isset($rule['letter'])) {
            $letter = self::getLetter();

            switch ($rule['letter']) {
                case 'lower':
                    $force_pool .= strtolower(substr($letter, mt_rand(0, strlen($letter) - 1), 1));
                    $letter = strtolower($letter);
                    break;

                case 'upper':
                    $force_pool .= strtoupper(substr($letter, mt_rand(0, strlen($letter) - 1), 1));
                    $letter = strtoupper($letter);
                    break;

                case 'lowerAndUpper':
                    $force_pool .= strtolower(substr($letter, mt_rand(0, strlen($letter) - 1), 1));
                    $force_pool .= strtoupper(substr($letter, mt_rand(0, strlen($letter) - 1), 1));
                    break;
                default:
                    $force_pool .= substr($letter, mt_rand(0, strlen($letter) - 1), 1);
                    break;
            }

            $pool .= $letter;
        }

        if (isset($rule['number'])) {
            $number = self::getNumber();

            if ('require' == $rule['number']) {
                $force_pool .= substr($number, mt_rand(0, strlen($number) - 1), 1);
            }

            $pool .= $number;
        }

        if (isset($rule['special'])) {
            $special = self::getSpecial();

            if ('require' == $rule['special']) {
                $force_pool .= substr($special, mt_rand(0, strlen($special) - 1), 1);
            }

            $pool .= $special;
        }

        $pool = str_shuffle($pool);

        return str_shuffle($force_pool.substr($pool, 0, $length - strlen($force_pool)));
    }

    private static function getLetter()
    {
        return 'AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz';
    }

    private static function getNumber()
    {
        return '1234567890';
    }

    private static function getSpecial()
    {
        return  '!@#$%&*+=-';
    }
}
