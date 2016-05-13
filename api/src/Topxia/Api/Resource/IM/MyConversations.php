<?php

namespace Topxia\Api\Resource\IM;

use Silex\Application;
use Topxia\Api\Resource\BaseResource;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\CloudPlatform\CloudAPIFactory;

class MyConversations extends BaseResource
{
    public function get(Application $app, Request $request)
    {
        $user = $this->getCurrentUser();

        $start = isset($fields['start']) ? (int) $fields['start'] : 0;
        $limit = isset($fields['limit']) ? (int) $fields['limit'] : 50;

        $myConversations = $this->getConversationService()->listMyConversationsByUserId($user['id'], $start, $limit);

        return $this->filter($myConversations);
    }

    public function filter($res)
    {
        return $this->multicallFilter('IM/MyConversation', $res);
    }

    protected function getConversationService()
    {
        return $this->getServiceKernel()->createService('IM.ConversationService');
    }
}
