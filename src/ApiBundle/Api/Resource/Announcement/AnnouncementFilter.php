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
        $data['simpleContent'] = $this->plainText(strip_tags($data['content']), 50);
        $data['startTime'] = empty($data['startTime']) ? 0 : date('c', $data['startTime']);
        $data['endTime'] = empty($data['endTime']) ? 0 : date('c', $data['endTime']);
    }

    protected function plainText($text, $count)
    {
        return mb_substr($text, 0, $count, 'utf-8');
    }
}
