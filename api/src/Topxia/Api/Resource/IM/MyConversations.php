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

        $conditions = array(
            'userId' => $user['id']
        );

        $orderBy = array(
            'updatedTime',
            'DESC'
        );
        $start = isset($fields['start']) ? (int) $fields['start'] : 0;
        $limit = isset($fields['limit']) ? (int) $fields['limit'] : 20;

        $myConversations = $this->getMyConversationService()->searchMyConversations(
            $conditions,
            $orderBy,
            $start,
            $limit
        );
        $total = $this->getMyConversationService()->searchMyConversationCount($conditions);

        return $this->wrap($this->filter($myConversations), $total);
    }

    public function filter($res)
    {
        return $this->multicallFilter('IM/MyConversation', $res);
    }

    protected function getMyConversationService()
    {
        return $this->getServiceKernel()->createService('IM.MyConversationService');
    }
}
