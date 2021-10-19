<?php

namespace ApiBundle\Api\Resource\LiveStatistic;

use ApiBundle\Api\Resource\Filter;

class LiveStatisticFilter extends Filter
{
    protected $simpleFields = [
        'id', 'title', 'startTime', 'endTime', 'length', 'status', 'maxStudentNum',
    ];
}
