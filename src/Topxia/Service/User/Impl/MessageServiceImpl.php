<?php
namespace Topxia\Service\User\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\User\MessageService;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Util\EdusohoTuiClient;

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
    
    public function sendMessage($fromId, $toId, $content, $type = 'text', $createdTime = null)
    {   
        if (empty($fromId) || empty($toId)) {
            throw $this->createServiceException("发件人或收件人未注册!"); 
        }

        if($fromId == $toId){
            throw $this->createServiceException("抱歉,不允许给自己发送私信!"); 
        }

        if(empty($content)){
            throw $this->createServiceException("抱歉,不能发送空内容!"); 
        }

        $createdTime = empty($createdTime) ? time() : $createdTime;
        $message = $this->addMessage($fromId, $toId, $content, $type, $createdTime);
        $this->prepareConversationAndRelationForSender($message, $toId, $fromId, $createdTime);
        $this->prepareConversationAndRelationForReceiver($message, $fromId, $toId, $createdTime);
        $this->getUserService()->waveUserCounter($toId, 'newMessageNum', 1);
        // $this->getDispatcher()->dispatch('message.service.send', new ServiceEvent($message));
        return $message;
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
            $this->safelyUpdateConversationMessageNum($conversation);
            $this->safelyUpdateConversationunreadNum($conversation);
        } else {
            $this->safelyUpdateConversationMessageNum($conversation);
        }
        
        $this->getRelationDao()->deleteConversationMessage($conversationId, $messageId);
        $relationCount = $this->getRelationDao()->getRelationCountByConversationId($conversationId);
        if($relationCount == 0){
            $this->getConversationDao()->deleteConversation($conversationId);
        }

    }

    public function deleteMessagesByIds(array $ids=null)
    {
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

    public function getConversationByFromIdAndToId($fromId, $toId)
    {
        return $this->getConversationDao()->getConversationByFromIdAndToId($fromId, $toId);
    }

    public function pullMessagesFromApi()
    {
        $user = $this->getCurrentUser();
        $messageSetting = $this->getSettingService()->get('message');
        if (empty($messageSetting)) {
            $messageSetting = array('lastMaxId' => 0);
            $this->getSettingService()->set('message', $messageSetting);
        }
        // $tuiClient = new EdusohoTuiClient();
        // $messages = $tuiClient->findMessagesByUserIdAndlastMaxId($user['id'], $messageSetting['lastMaxId']);
        // $lastMaxId = 0;
        // if (isset($messages['error'])) {
        //     throw $this->createServiceException('获取远程私信错误');
        // }
        // foreach ($messages as $message) {
        //     $messageSetting['lastMaxId'] = $message['id'];
        //     $this->sendMessage($message['userId'], $user['id'], $message['context'], $message['type'], $message['createdTime']);
        // }
        $this->getSettingService()->set('message', $messageSetting);
    }

    protected function addMessage($fromId, $toId, $content, $type, $createdTime)
    {
        $message = array(
            'fromId' => $fromId,
            'toId' => $toId,
            'type' => $type,
            'content' => $this->purifyHtml($content),
            'createdTime' => $createdTime,
        );
        return $this->getMessageDao()->addMessage($message);
    }


    protected function prepareConversationAndRelationForSender($message, $toId, $fromId, $createdTime)
    {
        $conversation = $this->getConversationDao()->getConversationByFromIdAndToId($toId, $fromId);
        if ($conversation) {
            $this->getConversationDao()->updateConversation($conversation['id'], array(
                'messageNum' => $conversation['messageNum'] + 1,
                'latestMessageUserId' => $message['fromId'],
                'latestMessageContent' => $message['content'],
                'latestMessageTime' => $message['createdTime'],
                'latestMessageType' => $message['type']
            ));
        } else {
            $conversation = array(
                'fromId' => $toId,
                'toId' => $fromId,
                'messageNum' => 1,
                'latestMessageUserId' => $message['fromId'],
                'latestMessageContent' => $message['content'],
                'latestMessageTime' => $message['createdTime'],
                'latestMessageType' => $message['type'],
                'unreadNum' => 0,
                'createdTime' => $createdTime,
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

    protected function prepareConversationAndRelationForReceiver($message, $fromId, $toId, $createdTime)
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
                'createdTime' => $createdTime,
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

    protected function safelyUpdateConversationMessageNum($conversation)
    {
        if ($conversation['messageNum'] <= 0) {
            $this->getConversationDao()->updateConversation($conversation['id'], 
            array('messageNum'=>0));
        } else {
            $this->getConversationDao()->updateConversation($conversation['id'], 
            array('messageNum'=>$conversation['messageNum']-1));
        }
    }

    protected function safelyUpdateConversationunreadNum($conversation)
    {
        if ($conversation['unreadNum'] <= 0) {
            $this->getConversationDao()->updateConversation($conversation['id'], 
            array('unreadNum'=>0));
        } else {
            $this->getConversationDao()->updateConversation($conversation['id'], 
            array('unreadNum'=>$conversation['unreadNum']-1));
        }
    }

    protected function sortMessages($messages)
    {
        usort($messages ,function($a, $b){
            if($a['createdTime'] > $b['createdTime']){
                return -1;
            }elseif($a['createdTime'] ==  $b['createdTime']){
                return 0;
            }elseif($a['createdTime'] < $b['createdTime']){
                return 1;
            }
        });
        return $messages;
    }

    protected function getMessageDao()
    {
        return $this->createDao('User.MessageDao');
    }

    protected function getConversationDao()
    {
        return $this->createDao('User.MessageConversationDao');
    }

    protected function getRelationDao()
    {
        return $this->createDao('User.MessageRelationDao');
    }

    protected function getUserService()
    {
        return $this->createService('User.UserService');
    }

    protected function getSettingService()
    {
        return $this->createService('System.SettingService');
    }


}