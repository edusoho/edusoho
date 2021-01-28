<?php

namespace AppBundle\Common;

/**
 * 一个简单的验证类.
 */
class SimpleValidator
{
    public static function email($value)
    {
        $value = (string) $value;
        $valid = filter_var($value, FILTER_VALIDATE_EMAIL);

        return false !== $valid;
    }

    public static function nickname($value, array $option = [])
    {
        $option = array_merge(
            ['minLength' => 4, 'maxLength' => 18],
            $option
        );

        $len = (strlen($value) + mb_strlen($value, 'utf-8')) / 2;

        if ($len > $option['maxLength'] || $len < $option['minLength']) {
            return false;
        }

        if (preg_match('/^1\d{10}$/', $value)) {
            return false;
        }

        return (bool) preg_match('/^[\x{4e00}-\x{9fa5}a-zA-z0-9_.·]+$/u', $value);
    }

    public static function password($value, array $option = [])
    {
        return (bool) preg_match('/^[\S]{5,20}$/u', $value);
    }

    public static function lowPassword($value, array $option = [])
    {
        return (bool) preg_match('/^[\S]{5,20}$/u', $value);
    }

    public static function middlePassword($value, array $option = [])
    {
        return (bool) preg_match('/^(?!^(\d+|[a-zA-Z]+|[^\s\da-zA-Z]+)$)^[\S]{8,20}$/u', $value);
    }

    public static function highPassword($value, array $option = [])
    {
        return (bool) preg_match('/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[^\s\da-zA-Z])[\S]{8,32}$/u', $value);
    }

    //真实姓名改成和nickname一样
    public static function truename($value, array $option = [])
    {
        $option = array_merge(
            ['minLength' => 4, 'maxLength' => 18],
            $option
        );

        $len = (strlen($value) + mb_strlen($value, 'utf-8')) / 2;

        if ($len > $option['maxLength'] || $len < $option['minLength']) {
            return false;
        }

        if (preg_match('/^1\d{10}$/', $value)) {
            return false;
        }

        return (bool) preg_match('/^[\x{4e00}-\x{9fa5}a-zA-z_.·]+$/u', $value);
    }

    public static function idcard($value)
    {
        return (bool) preg_match('/^\d{17}[0-9xX]$/', $value);
    }

    public static function bankCardId($value)
    {
        return (bool) preg_match('/^(\d{16,19})$/', $value);
    }

    public static function mobile($value)
    {
        return (bool) ((11 == strlen($value)) && preg_match('/^1\d{10}$/', $value));
    }

    public static function numbers($value)
    {
        return (bool) preg_match('/^(\d+,?)*\d+$/', $value);
    }

    public static function phone($value)
    {
        return (bool) preg_match('/^(\d{4}-|\d{3}-)?(\d{8}|\d{7})$/', $value);
    }

    public static function date($value)
    {
        return (bool) preg_match('/^(\d{4}|\d{2})-((0?([1-9]))|(1[0-2]))-((0?[1-9])|([12]([0-9]))|(3[0|1]))$/', $value);
    }

    public static function qq($value)
    {
        return (bool) preg_match('/^[1-9]\d{4,}$/', $value);
    }

    public static function weixin($value)
    {
        return (bool) preg_match('/^[-_a-zA-Z0-9]{6,20}$/', $value);
    }

    public static function integer($value)
    {
        return (bool) preg_match('/^[+-]?\d{1,9}$/', $value);
    }

    public static function float($value)
    {
        return (bool) preg_match('/^(([+-]?[1-9]{1}\d*)|([+-]?[0]{1}))(\.(\d){1,2})?$/i', $value);
    }

    public static function dateTime($value)
    {
        return (bool) preg_match('/^(?:(?!0000)[0-9]{4}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-8])|(?:0[13-9]|1[0-2])-(?:29|30)|(?:0[13578]|1[02])-31)|(?:[0-9]{2}(?:0[48]|[2468][048]|[13579][26])|(?:0[48]|[2468][048]|[13579][26])00)-02-29)$/', $value);
    }

    public static function site($value)
    {
        return (bool) preg_match('/^(http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?$/', $value);
    }

    public static function chineseAndAlphanumeric($value)
    {
        return (bool) preg_match('/^([\x{4e00}-\x{9fa5}]|[a-zA-Z0-9_.·])*$/u', $value);
    }
}
