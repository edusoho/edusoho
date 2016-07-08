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



    public function addMyConversation($myConversation)
    {
        $myConversation = $this->filterMyConversationFields($myConversation);
        $myConversation['createdTime'] = time();
        $myConversation['updatedTime'] = time();
        return $this->getMyConversationDao()->addMyConversation($myConversation);
    }

    public function updateMyConversationByNo($no, $fields)
    {
        return $this->getMyConversationDao()->updateMyConversationByNo($no, $fields);
    }

    public function listMyConversationsByUserId($userId, $start = 0, $limit = 1000)
    {
        $conditions = array(
            'userId' => $userId
        );

        $orderBy = array(
            'updatedTime',
            'DESC'
        );
        
        return $this->getMyConversationDao()->searchMyConversations(
            $conditions,
            $orderBy,
            $start,
            $limit
        );
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

    protected function filterMyConversationFields(array $fields)
    {
        $fields = ArrayToolkit::parts($fields, array(
            'no',
            'userId',
            'createdTime',
            'updatedTime'
        ));
        
        if (empty($fields['no'])) {
            throw $this->createServiceException('field `no` can not be empty');
        }

        if (empty($fields['userId'])) {
            throw $this->createServiceException('field `userId` can not be empty');
        }

        return $fields;
    }

    protected function getMyConversationDao()
    {
        return $this->createDao('IM.MyConversationDao');
    }

    protected function getConversationDao()
    {
        return $this->createDao('IM.ConversationDao');
    }
}
