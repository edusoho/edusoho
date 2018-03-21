<?php

namespace Codeages\Biz\Framework\Util;

class TimeMachine
{
    private static $mockedTime = 0;

    /**
     * 单元测试时，解决因为时间引起的测试报错问题
     */
    public static function time()
    {
        return empty(self::$mockedTime) ? time() : self::$mockedTime;
    }

    public static function setMockedTime($time)
    {
        self::$mockedTime = $time;
    }
}
