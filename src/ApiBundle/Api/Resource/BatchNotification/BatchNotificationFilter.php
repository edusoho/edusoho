<?php

namespace ApiBundle\Api\Resource\BatchNotification;

use ApiBundle\Api\Resource\Filter;

class BatchNotificationFilter extends Filter
{
    protected $publicFields = array(
        'id', 'type', 'title', 'fromId', 'content', 'targetType', 'targetId', 'createdTime', 'published', 'sendedTime'
    );

    protected function publicFields(&$data)
    {
        $data['content'] = $this->convertAbsoluteUrl($data['content']);
        $data['simpleContent'] = strip_tags($data['content']);
    }
}
