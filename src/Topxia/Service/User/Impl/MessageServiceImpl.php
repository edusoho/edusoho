<?php
namespace Topxia\Service\User\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\User\MessageService;
use Topxia\Common\ArrayToolkit;

class MessageServiceImpl extends BaseService implements MessageService
{   
    public function searchMessagesCount($conditions)
    {
        return $this->getMessageDao()->searchMessagesCount($conditions);
    }

    public function searchMessages($conditions, $sort, $start, $limit)
    {
        return $this->getMessageDao()->searchMessages($conditions, $sort, $start, $limit);
    }
    
    public function sendMessage($fromId, $toId, $content)
    {   
        if($fromId == $toId){
            throw $this->createServiceException("抱歉,不允许给自己发送私信!"); 
        }
        if(empty($content)){
            throw $this->createServiceException("抱歉,不允许发送没有内容的空私信!"); 
        }
        if(mb_strlen($content) > 500){
            throw $this->createServiceException("抱歉，请指定私信内容的大小在500字以内！");
        }
        $message = $this->addMessage($fromId, $toId, $content);
        $this->prepareConversationAndRelationForSender($message, $toId, $fromId);
        $this->prepareConversationAndRelationForReceiver($message, $fromId, $toId);
    }

    public function getConversation($conversationId)
    {
        return $this->getConversationDao()->getConversation($conversationId);
    }

    public function deleteConversationMessage($conversationId, $messageId)
    {
        $relation = $this->getRelationDao()->getRelationByConversationIdAndMessageId($conversationId, $messageId);
        $conversation = $this->getConversationDao()->getConversation($conversationId);
        if($relation['isRead'] == self::RELATION_ISREAD_OFF){
            $this->getConversationDao()->updateConversation($conversationId, 
                array('messageNum'=>$conversation['messageNum']-1,
                    'unreadNum'=>$conversation['unreadNum']-1));  
        } else {
            $this->getConversationDao()->updateConversation($conversationId, 
                array('messageNum'=>$conversation['messageNum']-1));
        }
        
        $this->getRelationDao()->deleteConversationMessage($conversationId, $messageId);
        $relationCount = $this->getRelationDao()->getRelationCountByConversationId($conversationId);
        if($relationCount == 0){
            $this->getConversationDao()->deleteConversation($conversationId);
        }

    }

    public function deleteMessagesByIds(array $ids=null){
        if(empty($ids)){
             throw $this->createServiceException("Please select message item !");
        }
        foreach ($ids as $id) {
            $message = $this->getMessageDao()->getMessage($id);
            $conversation = $this->getConversationDao()->getConversationByFromIdAndToId($message['fromId'], $message['toId']);
            if(!empty($conversation)){
                $this->deleteConversationMessage($conversation['id'], $message['id']);
            }
            
            $conversation = $this->getConversationDao()->getConversationByFromIdAndToId($message['toId'], $message['fromId']);
            if(!empty($conversation)){
                $this->deleteConversationMessage($conversation['id'], $message['id']);
            }

            $this->getMessageDao()->deleteMessage($id);
        }
            return true;
    }

    public function findUserConversations($userId, $start, $limit)
    {
        return $this->getConversationDao()->findConversationsByToId($userId, $start, $limit);
    }

    public function getUserConversationCount($userId)
    {
        return $this->getConversationDao()->getConversationCountByToId($userId);
    }

    public function getConversationMessageCount($conversationId)
    {
        return $this->getRelationDao()->getRelationCountByConversationId($conversationId);
    }

    public function deleteConversation($conversationId)
    {
        $this->getRelationDao()->deleteRelationByConversationId($conversationId);
        return $this->getConversationDao()->deleteConversation($conversationId);   
    }

    public function markConversationRead($conversationId)
    {   
        $conversation = $this->getConversationDao()->getConversation($conversationId);
        if (empty($conversation)) {
            throw $this->createServiceException(sprintf("私信会话#%s不存在。", $conversationId));
        }
        $updatedConversation = $this->getConversationDao()->updateConversation($conversation['id'], array('unreadNum'=>0));
        $this->getRelationDao()->updateRelationIsReadByConversationId($conversationId,array('isRead'=>1));
        return $updatedConversation;
    }

    public function findConversationMessages($conversationId, $start, $limit)
    {
        $relations = $this->getRelationDao()->findRelationsByConversationId($conversationId, $start, $limit);
        $messages = $this->getMessageDao()->findMessagesByIds(ArrayToolkit::column($relations, 'messageId'));
        $createUsers = $this->getUserService()->findUsersByIds(ArrayToolkit::column($messages, 'fromId'));

        foreach ($messages as &$message) {
            foreach ($createUsers as $createUser) {
               if($createUser['id'] == $message['fromId']){
                    $message['createdUser'] = $createUser;
               }
            }
        }
        return $this->sortMessages($messages);
    }

    public function clearUserNewMessageCounter($userId)
    {
        $this->getUserService()->clearUserCounter($userId, 'newMessageNum');
    }

    private function sortMessages($messages)
    {
        usort($messages ,function($a, $b){
            if($a['createdTime'] > $b['createdTime']) return -1;
            if($a['createdTime'] ==  $b['createdTime']) return 0;
            if($a['createdTime'] < $b['createdTime']) return 1;
        });
        return $messages;
    }

    public function getConversationByFromIdAndToId($fromId, $toId)
    {
        return $this->getConversationDao()->getConversationByFromIdAndToId($fromId, $toId);
    }

    private function addMessage($fromId, $toId, $content)
    {
        $message = array(
            'fromId' => $fromId,
            'toId' => $toId,
            'content' => $content,
            'createdTime' => time(),
        );
        return $this->getMessageDao()->addMessage($message);
    }


    private function prepareConversationAndRelationForSender($message, $toId, $fromId)
    {
        $conversation = $this->getConversationDao()->getConversationByFromIdAndToId($toId, $fromId);
        if ($conversation) {
            $this->getConversationDao()->updateConversation($conversation['id'], array(
                'messageNum' => $conversation['messageNum'] + 1,
                'latestMessageUserId' => $message['fromId'],
                'latestMessageContent' => $message['content'],
                'latestMessageTime' => $message['createdTime'],
            ));
        } else {
            $conversation = array(
                'fromId' => $toId,
                'toId' => $fromId,
                'messageNum' => 1,
                'latestMessageUserId' => $message['fromId'],
                'latestMessageContent' => $message['content'],
                'latestMessageTime' => $message['createdTime'],
                'unreadNum' => 0,
                'createdTime' => time(),
            );
        $conversation = $this->getConversationDao()->addConversation($conversation);
        }

        $relation = array(
            'conversationId' => $conversation['id'],
            'messageId' => $message['id'],
            'isRead' => 0
        );
        $relation = $this->getRelationDao()->addRelation($relation);

    }

    private function prepareConversationAndRelationForReceiver($message, $fromId, $toId)
    {
        $conversation = $this->getConversationDao()->getConversationByFromIdAndToId($fromId, $toId);
        if ($conversation) {
            $this->getConversationDao()->updateConversation($conversation['id'], array(
                'messageNum' => $conversation['messageNum'] + 1,
                'latestMessageUserId' => $message['fromId'],
                'latestMessageContent' => $message['content'],
                'latestMessageTime' => $message['createdTime'],
                'unreadNum' => $conversation['unreadNum'] + 1,
            ));
        } else {
            $conversation = array(
                'fromId' => $fromId,
                'toId' => $toId,
                'messageNum' => 1,
                'latestMessageUserId' => $message['fromId'],
                'latestMessageContent' => $message['content'],
                'latestMessageTime' => $message['createdTime'],
                'unreadNum' => 1,
                'createdTime' => time(),
            );
        $conversation = $this->getConversationDao()->addConversation($conversation);
        }
        $relation = array(
            'conversationId' => $conversation['id'],
            'messageId' => $message['id'],
            'isRead'=>0
        );
        $relation = $this->getRelationDao()->addRelation($relation);
    }


    private function getMessageDao()
    {
        return $this->createDao('User.MessageDao');
    }

    private function getConversationDao()
    {
        return $this->createDao('User.MessageConversationDao');
    }

    private function getRelationDao()
    {
        return $this->createDao('User.MessageRelationDao');
    }

    private function getUserService()
    {
        return $this->createService('User.UserService');
    }


}