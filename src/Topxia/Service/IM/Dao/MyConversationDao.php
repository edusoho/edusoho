<?php

namespace Topxia\Service\IM\Dao;

interface MyConversationDao
{
    public function getMyConversation($id);

    public function getMyConversationByNo($no);

    public function addMyConversation($myConversation);

    public function updateMyConversation($id, $fields);

    public function updateMyConversationByNo($no, $fields);

    public function searchMyConversations($conditions, $orderBy, $start, $limit);

    public function searchMyConversationCount($conditions);
}
