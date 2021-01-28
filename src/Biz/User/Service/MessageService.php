<?php

namespace Biz\User\Service;

interface MessageService
{
    const RELATION_ISREAD_ON = 1;
    const RELATION_ISREAD_OFF = 0;

    /**
     * 发送私信
     *
     * @param int    $fromId      发送者ID
     * @param int    $toId        接收者ID
     * @param string $content     私信内容
     * @param int    $createdTime
     *
     * @return array 私信的相关信息
     */
    public function sendMessage($fromId, $toId, $content, $createdTime = null);

    public function getConversation($conversationId);

    /**
     * 获取会话的若干条私信
     *
     * @param int $conversationId 会话ID
     * @param int $start          起始条数
     * @param int $limit          限制条数
     *
     * @return array 特定会话的若干条私信
     */
    public function findConversationMessages($conversationId, $start, $limit);

    /**
     * 获取会话的私信条数.
     *
     * @param int $conversationId 会话ID
     *
     * @return int 特定会话的私信条数
     */
    public function countConversationMessages($conversationId);

    /**
     * 获取用户的会话条数.
     *
     * @param int $userId 用户ID
     *
     * @return int 特定用户的会话条数
     */
    public function countUserConversations($userId);

    /**
     * 获取指定用户的若干个会话.
     *
     * @param int $userId 用户ID
     * @param int $start  起始条数
     * @param int $limit  限制条数
     *
     * @return array 特定用户的若干条会话
     */
    public function findUserConversations($userId, $start, $limit);

    /**
     * 删除会话中的某条私信
     *
     * @param int $conversationId 指定的会话ID
     * @param int $messageId      指定的私信ID
     *
     * @return array 被删除的映射Relation
     */
    public function deleteConversationMessage($conversationId, $messageId);

    /**
     * 删除某一会话.
     *
     * @param int $conversationId 特定的会话ID
     *
     * @return array 被删除的会话
     */
    public function deleteConversation($conversationId);

    /**
     * 标记会话为已读
     * 1:未读数目全部为0
     * 2:相关的relation都为IsRead=1.
     *
     * @param int $conversationId 会话ID
     */
    public function markConversationRead($conversationId);

    /**通过会话拥有者和接受者来查询特定的会话
     *
     * @param integer $fromId 会话的接受者
     * @param integer $toId   会话的拥有者
     *                        $return array 会话
     */
    public function getConversationByFromIdAndToId($fromId, $toId);

    /**
     * 搜索特定状态下的私信条数.
     *
     * @param array $conditions 搜索条件
     *
     * @return int 搜索出的私信数目
     */
    public function countMessages($conditions);

    /**
     * 搜索特定状态下的私信
     *
     * @param array $conditions 搜索条件
     * @param array $sort       排序规则
     * @param int   $start      起始数目
     * @param int   $limit      区间条数
     *
     * @return array 搜索到的私信内容
     */
    public function searchMessages($conditions, $sort, $start, $limit);

    /**
     * 删除特定id的私信
     *
     * @param array $ids 指定私信的id
     *
     * @return true 总算删除成功
     */
    public function deleteMessagesByIds(array $ids = null);

    public function clearUserNewMessageCounter($userId);

    public function findNewUserConversations($userId, $start, $limit);
}
