<?php

namespace AppBundle\Common;

class OrderToolkit
{
    public function reomveUnneededLogs($orderLogs)
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
