<?php

namespace AppBundle\Common;

use DateTime;

class DateToolkit
{
    /**
     * Generate a date range starting from startDate to endDate.
     *
     * @param [type] $startDate [description]
     * @param [type] $endDate   [description]
     *
     * @return [type] [description]
     */
    public static function generateDateRange($startDate, $endDate)
    {
        $startTime = strtotime($startDate);
        $endTime = strtotime($endDate);

        $range = range($startTime, $endTime, 3600 * 24);
        array_walk($range, function (&$value) {
            $value = date('Y-m-d', $value);
        });

        return $range;
    }

    public static function isToday($unixTime)
    {
        return date('Y-m-d', time()) == date('Y-m-d', $unixTime);
    }

    /**
     * @return int
     */
    public static function getMicroSecond()
    {
        return (int) (microtime(true) * 1000000);
    }

    public static function countWeekdaysInDateRange($startDate, $endDate, $weekdays)
    {
        $count = 0;
        $currentDate = new DateTime($startDate);
        $endDate = new DateTime($endDate);
        while ($currentDate <= $endDate) {
            if (in_array($currentDate->format('N'), $weekdays)) {
                $count++;
            }
            $currentDate->modify('+1 day');
        }

        return $count;
    }

    public static function convertToZHWeekday($weekday)
    {
        $zhWeekdays = [
            1 => '周一',
            2 => '周二',
            3 => '周三',
            4 => '周四',
            5 => '周五',
            6 => '周六',
            7 => '周日'
        ];

        return $zhWeekdays[$weekday] ?? '';
    }
}
