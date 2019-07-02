<?php

namespace AppBundle\Common;

use AppBundle\Common\Exception\RuntimeException;

class ClassroomToolkit
{
    public static function buildMemberDeadline(array $expiryDate)
    {
        $deadline = $expiryDate['expiryValue'];

        if ($expiryDate['expiryMode'] == 'days') {
            $deadline = time() + $expiryDate['expiryValue'] * 24 * 60 * 60;
        }

        if ($expiryDate['expiryMode'] == 'date') {
            if ($deadline < time()) {
                throw new RuntimeException('有效期的设置时间小于当前时间！');
            }
        }

        return $deadline;
    }
}
