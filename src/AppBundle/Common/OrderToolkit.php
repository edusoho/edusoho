<?php

namespace AppBundle\Common;

class OrderToolkit
{
    public static function removeUnneededLogs($orderLogs)
    {
        $result = array();
        foreach ($orderLogs as $key => $value) {
            if ('order.success' != $value['status']) {
                $result[] = $value;
            }
        }

        return $result;
    }
}
