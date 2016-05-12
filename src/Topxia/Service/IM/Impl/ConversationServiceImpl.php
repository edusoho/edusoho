<?php

namespace Topxia\Service\IM\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\IM\ConversationService;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceEvent;

class ConversationServiceImpl extends BaseService implements ConversationService
{

    public function getConversationByMemberIds(array $memberIds)
    {
        sort($memberIds);
        $memberHash = $this->buildMemberHash($memberIds);
        return $this->getConversationDao()->getConversationByMemberHash($memberHash);
    }

    public function addConversation($conversation)
    {
        $conversation = $this->filterConversationFields($conversation);

        if (count($conversation['memberIds']) < 2) {
            throw $this->createServiceException("Only support memberIds's count >= 2");
        }

        $conversation['memberHash'] = $this->buildMemberHash($conversation['memberIds']);
        $conversation['createdTime'] = time();

        return $this->getConversationDao()->addConversation($conversation);
    }

    protected function filterConversationFields(array $fields)
    {
        $fields = ArrayToolkit::parts($fields, array('no', 'memberIds'));

        if (empty($fields['no'])) {
            throw $this->createServiceException('field `no` can not be empty');
        }

        if (!is_array($fields['memberIds'])) {
            throw $this->createServiceException('field `memberIds` must be array');
        }
        if (empty($fields['memberIds'])) {
            throw $this->createServiceException('field `memberIds` can not be empty');
        }
        sort($fields['memberIds']);

        return $fields;
    }

    protected function buildMemberHash(array $memberIds)
    {
        return md5(join($memberIds, ','));
    }

    protected function getConversationDao()
    {
        return $this->createDao('IM.ConversationDao');
    }
}
