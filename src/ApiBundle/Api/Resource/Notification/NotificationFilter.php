<?php

namespace ApiBundle\Api\Resource\Notification;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Util\AssetHelper;
use Topxia\Service\Common\ServiceKernel;

class NotificationFilter extends Filter
{
    protected $publicFields = array(
        'id', 'userId', 'type', 'content','createdTime'
    );

    protected function publicFields(&$data)
    {
        $data['data'] = $data['content'];

        if (!empty($data['content']['content'])) {
            $data['content']['content'] = $this->convertAbsoluteUrl($data['content']['content']);
        }
        
        $data['content'] = trim(AssetHelper::renderView("ApiBundle:notification:{$data['type']}.tpl.html.twig", array('notification' => $data)));

        $data['userAvatar'] = null;
        if ($data['type'] == 'user-follow') {
            $user = $this->getUserService()->getUser($data['data']['userId']);
            $data['userAvatar'] = AssetHelper::getFurl($user['smallAvatar']);
        }
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User:UserService');
    }
}