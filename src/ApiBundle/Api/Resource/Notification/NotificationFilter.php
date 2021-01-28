<?php

namespace ApiBundle\Api\Resource\Notification;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Util\AssetHelper;
use ApiBundle\Api\Resource\User\UserFilter;

class NotificationFilter extends Filter
{
    protected $publicFields = array(
        'id', 'userId', 'type', 'content', 'createdTime',
    );

    protected function publicFields(&$data)
    {
        $data['data'] = $data['content'];

        if (!empty($data['content']['content'])) {
            $data['content']['content'] = $this->convertAbsoluteUrl($data['content']['content']);
        }

        $data['content'] = trim(AssetHelper::renderView("ApiBundle:notification:{$data['type']}.tpl.html.twig", array('notification' => $data)));

        if ($data['type'] == 'user-follow') {
            $userFilter = new UserFilter();
            $userFilter->setMode(Filter::SIMPLE_MODE);
            $userFilter->filter($data['data']['followUser']);
        }
    }
}
