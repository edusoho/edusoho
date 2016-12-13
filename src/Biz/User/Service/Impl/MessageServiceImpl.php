<?php
namespace Biz\User\Impl;

use Biz\BaseService;
use Biz\User\MessageService;
use Topxia\Common\ArrayToolkit;

class MessageServiceImpl extends BaseService implements MessageService
{
    public function count($conditions)
    {
        return $this->getMessageDao()->count($conditions);
    }

    public function searchMessages($conditions, $sort, $start, $limit)
    {
        return $this->getMessageDao()->search($conditions, $sort, $start, $limit);
    }

    public function sendMessage($fromId, $toId, $content, $type = 'text', $createdTime = null)
    {
        if (empty($fromId) || empty($toId)) {
            throw $this->createServiceException('发件人或收件人未注册!');
        }

        if ($fromId == $toId) {
            throw $this->createServiceException('抱歉,不允许给自己发送私信!');
        }

        if (empty($content)) {
            throw $this->createServiceException('抱歉,不能发送空内容!');
        }

        $createdTime = empty($createdTime) ? time() : $createdTime;
        $message     = $this->addMessage($fromId, $toId, $content, $type, $createdTime);
        $this->prepareConversationAndRelationForSender($message, $toId, $fromId, $createdTime);
        $this->prepareConversationAndRelationForReceiver($message, $fromId, $toId, $createdTime);
        $this->getUserService()->waveUserCounter($toId, 'newMessageNum', 1);
        return $message;
    }

    public function getConversation($conversationId)
    {
        return $this->getConversationDao()->get($conversationId);
    }

    public function deleteConversationMessage($conversationId, $messageId)
    {
        $relation     = $this->getRelationDao()->getByConversationIdAndMessageId($conversationId, $messageId);
        $conversation = $this->getConversationDao()->get($conversationId);

        if ($relation['isRead'] == self::RELATION_ISREAD_OFF) {
            $this->safelyUpdateConversationMessageNum($conversation);
            $this->safelyUpdateConversationunreadNum($conversation);
        } else {
            $this->safelyUpdateConversationMessageNum($conversation);
        }

        $this->getRelationDao()->deleteByConversationIdAndMessageId($conversationId, $messageId);
        $relationCount = $this->getRelationDao()->countByConversationId($conversationId);
        if ($relationCount == 0) {
            $this->getConversationDao()->delete($conversationId);
        }
    }

    public function deleteMessagesByIds(array $ids = null)
    {
        if (empty($ids)) {
            throw $this->createServiceException("Please select message item !");
        }
        foreach ($ids as $id) {
            $message      = $this->getMessageDao()->get($id);
            $conversation = $this->getConversationDao()->getByFromIdAndToId($message['fromId'], $message['toId']);
            if (!empty($conversation)) {
                $this->deleteByConversationIdAndMessageId($conversation['id'], $message['id']);
            }

            $conversation = $this->getConversationDao()->getByFromIdAndToId($message['toId'], $message['fromId']);
            if (!empty($conversation)) {
                $this->deleteByConversationIdAndMessageId($conversation['id'], $message['id']);
            }

            $this->getMessageDao()->delete($id);
        }
        return true;
    }

    public function findUserConversations($userId, $start, $limit)
    {
        return $this->getConversationDao()->findByToId($userId, $start, $limit);
    }

    public function getUserConversationCount($userId)
    {
        return $this->getConversationDao()->countByToId($userId);
    }

    public function getConversationMessageCount($conversationId)
    {
        return $this->getRelationDao()->countByConversationId($conversationId);
    }

    public function deleteConversation($conversationId)
    {
        $this->getRelationDao()->delete($conversationId);
        return $this->getConversationDao()->delete($conversationId);
    }

    public function markConversationRead($conversationId)
    {
        $conversation = $this->getConversationDao()->get($conversationId);
        if (empty($conversation)) {
            throw $this->createServiceException(sprintf('私信会话#%conversationId%不存在。', array('%conversationId%' => $conversationId)));
        }
        $updatedConversation = $this->getConversationDao()->update($conversation['id'], array('unreadNum' => 0));
        $this->getRelationDao()->updateRelationIsReadByConversationId($conversationId, array('isRead' => 1));
        return $updatedConversation;
    }

    public function findConversationMessages($conversationId, $start, $limit)
    {
        $relations   = $this->getRelationDao()->findByConversationId($conversationId, $start, $limit);
        $messages    = $this->getMessageDao()->findByIds(ArrayToolkit::column($relations, 'messageId'));
        $createUsers = $this->getUserService()->findByIds(ArrayToolkit::column($messages, 'fromId'));

        foreach ($messages as &$message) {
            foreach ($createUsers as $createUser) {
                if ($createUser['id'] == $message['fromId']) {
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
        return $this->getConversationDao()->getByFromIdAndToId($fromId, $toId);
    }

    protected function addMessage($fromId, $toId, $content, $type, $createdTime)
    {
        $message = array(
            'fromId'      => $fromId,
            'toId'        => $toId,
            'type'        => $type,
            'content'     => $this->purifyHtml($content),
            'createdTime' => $createdTime
        );
        return $this->getMessageDao()->create($message);
    }

    protected function prepareConversationAndRelationForSender($message, $toId, $fromId, $createdTime)
    {
        $conversation = $this->getConversationDao()->getByFromIdAndToId($toId, $fromId);
        if ($conversation) {
            $this->getConversationDao()->update($conversation['id'], array(
                'messageNum'           => $conversation['messageNum'] + 1,
                'latestMessageUserId'  => $message['fromId'],
                'latestMessageContent' => $message['content'],
                'latestMessageTime'    => $message['createdTime'],
                'latestMessageType'    => $message['type']
            ));
        } else {
            $conversation = array(
                'fromId'               => $toId,
                'toId'                 => $fromId,
                'messageNum'           => 1,
                'latestMessageUserId'  => $message['fromId'],
                'latestMessageContent' => $message['content'],
                'latestMessageTime'    => $message['createdTime'],
                'latestMessageType'    => $message['type'],
                'unreadNum'            => 0,
                'createdTime'          => $createdTime
            );
            $conversation = $this->getConversationDao()->create($conversation);
        }

        $relation = array(
            'conversationId' => $conversation['id'],
            'messageId'      => $message['id'],
            'isRead'         => 0
        );
        $relation = $this->getRelationDao()->create($relation);
    }

    protected function prepareConversationAndRelationForReceiver($message, $fromId, $toId, $createdTime)
    {
        $conversation = $this->getConversationDao()->getByFromIdAndToId($fromId, $toId);
        if ($conversation) {
            $this->getConversationDao()->update($conversation['id'], array(
                'messageNum'           => $conversation['messageNum'] + 1,
                'latestMessageUserId'  => $message['fromId'],
                'latestMessageContent' => $message['content'],
                'latestMessageTime'    => $message['createdTime'],
                'unreadNum'            => $conversation['unreadNum'] + 1
            ));
        } else {
            $conversation = array(
                'fromId'               => $fromId,
                'toId'                 => $toId,
                'messageNum'           => 1,
                'latestMessageUserId'  => $message['fromId'],
                'latestMessageContent' => $message['content'],
                'latestMessageTime'    => $message['createdTime'],
                'unreadNum'            => 1,
                'createdTime'          => $createdTime
            );
            $conversation = $this->getConversationDao()->create($conversation);
        }
        $relation = array(
            'conversationId' => $conversation['id'],
            'messageId'      => $message['id'],
            'isRead'         => 0
        );
        $relation = $this->getRelationDao()->create($relation);
    }

    protected function safelyUpdateConversationMessageNum($conversation)
    {
        if ($conversation['messageNum'] <= 0) {
            $this->getConversationDao()->update($conversation['id'],
                array('messageNum' => 0));
        } else {
            $this->getConversationDao()->update($conversation['id'],
                array('messageNum' => $conversation['messageNum'] - 1));
        }
    }

    protected function safelyUpdateConversationunreadNum($conversation)
    {
        if ($conversation['unreadNum'] <= 0) {
            $this->getConversationDao()->update($conversation['id'],
                array('unreadNum' => 0));
        } else {
            $this->getConversationDao()->update($conversation['id'],
                array('unreadNum' => $conversation['unreadNum'] - 1));
        }
    }

    protected function sortMessages($messages)
    {
        usort($messages, function ($a, $b) {
            if ($a['createdTime'] > $b['createdTime']) {
                return -1;
            } elseif ($a['createdTime'] == $b['createdTime']) {
                return 0;
            } elseif ($a['createdTime'] < $b['createdTime']) {
                return 1;
            }
        });
        return $messages;
    }

    protected function getMessageDao()
    {
        return $this->createDao('User:MessageDao');
    }

    protected function getConversationDao()
    {
        return $this->createDao('User:MessageConversationDao');
    }

    protected function getRelationDao()
    {
        return $this->createDao('User:MessageRelationDao');
    }

    protected function getUserService()
    {
        return $this->biz->service('User:UserService');
    }

    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }
}
