<?php

namespace AppBundle\Common;

use AppBundle\Common\Exception\UnexpectedValueException;

class ClassroomToolkit
{
    public static function buildMemberDeadline(array $expiryDate)
    {
        $deadline = $expiryDate['expiryValue'];

        if ('days' == $expiryDate['expiryMode']) {
            $deadline = time() + $expiryDate['expiryValue'] * 24 * 60 * 60;
        }

        if ('date' == $expiryDate['expiryMode']) {
            if ($deadline < time()) {
                throw new UnexpectedValueException('有效期的设置时间小于当前时间！');
            }
        }

        return $deadline;
    }
}
