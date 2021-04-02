<?php

namespace AppBundle\Common;

class PasswordGenerateToolkit
{
    private static $passwordType = ['low', 'middle', 'high'];

    public static function create($passwordType)
    {
        if (!in_array($passwordType, self::$passwordType)) {
            throw new \Exception('Password type not allowed');
        }

        $defaultRule = ['number' => 'need'];
        $defaultLength = 4;

        switch ($passwordType) {
            case 'low':
                $defaultLength = mt_rand(6, 20);
                break;
            case 'middle':
                $defaultRule = ['letter' => 'lower', 'number' => 'need'];
                $defaultLength = mt_rand(9, 20);
                break;
            case 'high':
                $defaultRule = ['letter' => 'lowerAndUpper', 'number' => 'need', 'special' => 'need'];
                $defaultLength = mt_rand(9, 32);
                break;
            default:
                break;
        }

        return self::generate($defaultLength, $defaultRule);
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

            if ('need' == $rule['number']) {
                $force_pool .= substr($number, mt_rand(0, strlen($number) - 1), 1);
            }

            $pool .= $number;
        }

        if (isset($rule['special'])) {
            $special = self::getSpecial();

            if ('need' == $rule['special']) {
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
