<?php

namespace AppBundle\Common;

class UserToolkit
{
    public static function isEmailGeneratedBySystem($email)
    {
        if ('@edusoho.net' == strstr($email, '@')) {
            return true;
        }

        return false;
    }

    public static function isGenderDefault($gender)
    {
        return 'secret' == $gender;
    }
}
