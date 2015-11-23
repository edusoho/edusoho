<?php
namespace Custom\Common;

use Topxia\Common\SimpleValidator as BaseValidator;

class SimpleValidator extends BaseValidator
{
    public static function staffNo($value)
    {
        return !!preg_match('/\d{5,12}$/', $value);
    }

    public static function mobile($value)
    {
        return !!preg_match('/^1[3|5|7|8]\d{9}$/', $value);
    }
}
