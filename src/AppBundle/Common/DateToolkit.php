<?php

namespace AppBundle\Common;

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
}
