<?php

namespace Topxia\Service\IM\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\IM\ConversationService;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceEvent;

class ConversationServiceImpl extends BaseService implements ConversationService {

    public function getConversationByUserIds(array $userIds)
    {
        sort($userIds);
        return $this->getConversationDao()->getConversationByUserIds($userIds);
    }

    public function addConversation($conversation)
    {
        $userIds = $conversation['userIds'];
        if (empty($userIds) && !is_array($userIds) && count($userIds) < 2) {
            throw $this->createServiceException("会话人数不能少于2人");
        }
        sort($conversation['userIds']);
        return $this->getConversationDao()->addConversation($conversation);
    }

    protected function getConversationDao() 
    {
        return $this->createDao('IM.ConversationDao');
    }
}
