<?php
/**
 * Created by PhpStorm.
 * User: retamia
 * Date: 15/9/18
 * Time: 13:49
 */

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