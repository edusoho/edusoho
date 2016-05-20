<?php

namespace Topxia\Common;

class DateToolkit
{
    /**
     * 如果时间筛选开始和结束的日期一直，则因为忽略了单位秒导致无法筛选到数据
     * 2016-05-20 00:00:39 ->2016-05-20 00:00
     * @param [strtotime] $endDateTime
     */
    public static function strtotimeAppendSecond($date)
    {
        $time = strtotime($date);
        $date = date('Y-m-d H:i:s', strtotime('+1 minute', $time));
        return strtotime($date);
    }
}
