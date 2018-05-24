<?php

namespace ApiBundle\Api\Util;

class OrderUtil
{
    public function reomveUnneededLogs($orderLogs)
    {
        foreach ($orderLogs as $key => $value) {
            if ('order.success' == $value['status']) {
                unset($orderLogs[$key]);
            }
        }

        return $orderLogs;
    }
}
