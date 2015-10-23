<?php

namespace Topxia\Service\User\Dao;

interface MessageConversationDao
{
    public function addConversation($conversation);

    public function getConversation($id);

    public function deleteConversation($id);

    public function updateConversation($id, $toUpdateConversation);

    public function getConversationByFromIdAndToId($fromId, $toId);
    
    public function findConversationsByToId($toId, $start, $limit);

    public function getConversationCountByToId($toId);
    
}