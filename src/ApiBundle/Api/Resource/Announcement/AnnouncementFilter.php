<?php

namespace ApiBundle\Api\Resource\Announcement;

use ApiBundle\Api\Resource\Filter;

class AnnouncementFilter extends Filter
{
    protected $publicFields = array(
        'id', 'userId', 'targetType', 'targetId', 'url', 'startTime', 'endTime', 'content', 'createdTime', 'updatedTime',
    );

    protected function publicFields(&$data)
    {
        $data['content'] = $this->convertAbsoluteUrl($data['content']);
    }
}
