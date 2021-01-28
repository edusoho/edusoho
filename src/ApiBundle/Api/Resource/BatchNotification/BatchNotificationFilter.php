<?php

namespace ApiBundle\Api\Resource\BatchNotification;

use ApiBundle\Api\Resource\Filter;

class BatchNotificationFilter extends Filter
{
    protected $publicFields = array(
        'id', 'type', 'title', 'fromId', 'content', 'targetType', 'targetId', 'createdTime', 'published', 'sendedTime',
    );

    protected function publicFields(&$data)
    {
        $data['content'] = $this->convertAbsoluteUrl($data['content']);
        $data['simpleContent'] = $this->plainText(strip_tags($data['content']), 50);
        $data['sendedTime'] = empty($data['sendedTime']) ? 0 : date('c', $data['sendedTime']);
    }

    protected function plainText($text, $count)
    {
        return mb_substr($text, 0, $count, 'utf-8');
    }
}
