<?php

namespace Biz\User\Dao\Impl;

use Biz\User\Dao\MessageConversationDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class MessageConversationDaoImpl extends GeneralDaoImpl implements MessageConversationDao
{
    protected $table = 'message_conversation';

    public function getByFromIdAndToId($fromId, $toId)
    {
        return $this->getByFields(array('fromId' => $fromId, 'toId' => $toId));
    }

    public function declares()
    {
        return array(
            'orderbys' => array('latestMessageTime'),
            'conditions' => array(
                'toId = :toId',
                'unreadNum > :lessUnreadNum',
            ),
        );
    }
}
