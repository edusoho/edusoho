<?php

namespace Topxia\Service\IM\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\IM\MyConversationService;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceEvent;

class MyConversationServiceImpl extends BaseService implements MyConversationService
{

    public function getMyConversation($id)
    {
        return $this->getMyConversationDao()->getMyConversation($id);
    }

    public function getMyConversationByNo($no)
    {
        return $this->getMyConversationDao()->getMyConversationByNo($no);
    }

    public function findMyConversationsByUserId($userId)
    {
        return $this->getMyConversationDao()->findMyConversationsByUserId($userId);
    }

    public function addMyConversation($myConversation)
    {
        $myConversation = $this->filterMyConversationFields($myConversation);
        $myConversation['createdTime'] = time();
        $myConversation['updatedTime'] = time();
        return $this->getMyConversationDao()->addMyConversation($myConversation);
    }

    public function updateMyConversation($id, $fields)
    {
        return $this->getMyConversationDao()->updateMyConversation($id, $fields);
    }

    public function updateMyConversationByNo($no, $fields)
    {
        return $this->getMyConversationDao()->updateMyConversationByNo($no, $fields);
    }

    public function searchMyConversations($conditions, $orderBy, $start, $limit)
    {
        return $this->getMyConversationDao()->searchMyConversations($conditions, $orderBy, $start, $limit);
    }

    public function searchMyConversationCount($conditions)
    {
        return $this->getMyConversationDao()->searchMyConversationCount($conditions);
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
}
