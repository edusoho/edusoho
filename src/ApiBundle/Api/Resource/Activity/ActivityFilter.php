<?php

namespace ApiBundle\Api\Resource\Activity;

use ApiBundle\Api\Resource\CourseSet\CourseSetFilter;
use ApiBundle\Api\Resource\Filter;

class ActivityFilter extends Filter
{
    protected $publicFields = array(
        'id', 'remark', 'ext', 'mediaType', 'mediaId'
    );

    protected function publicFields(&$data)
    {
        if (!empty($data['ext']) && $data['mediaType'] == 'live') {
            $data['replayStatus'] = $data['ext']['replayStatus'];
        }

        unset($data['ext']);
        unset($data['mediaType']);
    }
}