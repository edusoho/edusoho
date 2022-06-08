<?php

namespace AppBundle\Common;

class UserToolkit
{
    public static function isEmailGeneratedBySystem($email)
    {
        return (bool) preg_match('/^user_[a-z0-9]{9}@edusoho\.net$/', $email);
    }

    public static function isGenderDefault($gender)
    {
        return 'secret' == $gender;
    }
}
