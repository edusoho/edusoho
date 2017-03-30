<?php

namespace ApiBundle\Api\Resource\OrderInfo;

use ApiBundle\Api\Resource\Filter;

class OrderInfoFilter extends Filter
{
    protected function customFilter(&$data)
    {
        $data['title'] = $data[$data['targetType']]['title'];
        unset($data[$data['targetType']]);
        unset($data['users']);


    }
}