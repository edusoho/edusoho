<?php

namespace Topxia\Service\Common;

class FieldChecker
{
    public static function checkFieldName($name)
    {
        if (!ctype_alnum(str_replace('_', '', $name))) {
            throw new \InvalidArgumentException('Field name is invalid.');
        }

        return true;
    }
}
