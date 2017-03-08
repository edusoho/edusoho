<?php
namespace Topxia\Common;

use Topxia\Service\Common\ServiceKernel;

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
                throw new \Exception(self::getKernel()->trans('有效期的设置时间小于当前时间！'));
            }
        }

        return $deadline;
    }

    protected static function getKernel()
    {
        return ServiceKernel::instance();
    }
}