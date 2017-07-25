<?php

namespace ApiBundle\Api\Resource\Notification;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Util\AssetHelper;

class NotificationFilter extends Filter
{
    protected $publicFields = array(
        'id', 'userId', 'type', 'content','batchId', 'createdTime', 'isRead'
    );

    protected function publicFields(&$data)
    {
        if (!empty($data['content']['content'])) {
            $data['content']['content'] = $this->convertAbsoluteUrl($data['content']['content']);
        }
        
        $data['content'] = trim(AssetHelper::renderView("ApiBundle:notification:{$data['type']}.tpl.html.twig", array('notification' => $data)));
    }
}