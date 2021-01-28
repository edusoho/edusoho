<?php

namespace Biz\User\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\User\MessageException;
use Biz\User\Service\MessageService;

class MessageServiceImpl extends BaseService implements MessageService
{
    public function countMessages($conditions)
    {
        $conditions = $this->filterMessageConditions($conditions);

        return $this->getMessageDao()->count($conditions);
    }

    public function searchMessages($conditions, $order, $start, $limit)
    {
        $conditions = $this->filterMessageConditions($conditions);

        return $this->getMessageDao()->search($conditions, $order, $start, $limit);
    }

    public function sendMessage($fromId, $toId, $content, $type = 'text', $createdTime = null)
    {
        if (empty($fromId) || empty($toId)) {
            $this->createNewException(MessageException::NOTFOUND_SENDER_OR_RECEIVER());
        }

        if ($fromId == $toId) {
            $this->createNewException(MessageException::SEND_TO_SELF());
        }

        if (empty($content)) {
            $this->createNewException(MessageException::EMPTY_MESSAGE());
        }

        $createdTime = empty($createdTime) ? time() : $createdTime;
        $message = $this->addMessage($fromId, $toId, $content, $type, $createdTime);
        $this->prepareConversationAndRelationForSender($message, $toId, $fromId, $createdTime);
        $this->prepareConversationAndRelationForReceiver($message, $fromId, $toId, $createdTime);
        $conversation = $this->getConversationDao()->getByFromIdAndToId($fromId, $toId);
        if (1 == $conversation['unreadNum']) {
            $this->getUserService()->waveUserCounter($toId, 'newMessageNum', 1);
        }

        return $message;
    }

    public function getConversation($conversationId)
    {
        return $this->getConversationDao()->get($conversationId);
    }

    public function deleteConversationMessage($conversationId, $messageId)
    {
        $relation = $this->getRelationDao()->getByConversationIdAndMessageId($conversationId, $messageId);
        $conversation = $this->getConversationDao()->get($conversationId);

        if (self::RELATION_ISREAD_OFF == $relation['isRead']) {
            $this->safelyUpdateConversationMessageNum($conversation);
            $this->safelyUpdateConversationunreadNum($conversation);
        } else {
            $this->safelyUpdateConversationMessageNum($conversation);
        }

        $this->getRelationDao()->deleteByConversationIdAndMessageId($conversationId, $messageId);
        $relationCount = $this->getRelationDao()->count(['conversationId' => $conversationId]);
        if (0 == $relationCount) {
            $this->getConversationDao()->delete($conversationId);
        }
    }

    public function deleteMessagesByIds(array $ids = null)
    {
        if (empty($ids)) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }
        foreach ($ids as $id) {
            $message = $this->getMessageDao()->get($id);
            $conversation = $this->getConversationDao()->getByFromIdAndToId($message['fromId'], $message['toId']);
            if (!empty($conversation)) {
                $fields = [
                    'latestMessageContent' => '一条私信被删除。',
                ];
                $this->getConversationDao()->update($conversation['id'], $fields);
            }

            $conversation = $this->getConversationDao()->getByFromIdAndToId($message['toId'], $message['fromId']);
            if (!empty($conversation)) {
                $fields = [
                    'latestMessageContent' => '一条私信被删除。',
                ];
                $this->getConversationDao()->update($conversation['id'], $fields);
            }

            $fields = [
                'isDelete' => 1,
                ];
            $this->getMessageDao()->update($id, $fields);
        }

        return true;
    }

    public function findNewUserConversations($userId, $start, $limit)
    {
        $conditions = ['toId' => $userId, 'lessUnreadNum' => 0];

        return $this->getConversationDao()->search($conditions, ['latestMessageTime' => 'DESC'], $start, $limit);
    }

    public function findUserConversations($userId, $start, $limit)
    {
        $conditions = ['toId' => $userId];

        return $this->getConversationDao()->search($conditions, ['latestMessageTime' => 'DESC'], $start, $limit);
    }

    public function countUserConversations($userId)
    {
        return $this->getConversationDao()->count(['toId' => $userId]);
    }

    public function countConversationMessages($conversationId)
    {
        return $this->getRelationDao()->count(['conversationId' => $conversationId]);
    }

    public function deleteConversation($conversationId)
    {
        $this->getRelationDao()->deleteByConversationId($conversationId);
        $messageConversation = $this->getConversationDao()->get($conversationId);
        if ($messageConversation && $messageConversation['unreadNum'] > 0) {
            $user = $this->getCurrentUser();
            $this->getUserService()->updateUserNewMessageNum($user['id'], 1);
        }

        return $this->getConversationDao()->delete($conversationId);
    }

    public function markConversationRead($conversationId)
    {
        $conversation = $this->getConversationDao()->get($conversationId);
        if (empty($conversation)) {
            $this->createNewException(MessageException::NOTFOUND_CONVERSATION());
        }
        $updatedConversation = $this->getConversationDao()->update($conversation['id'], ['unreadNum' => 0]);
        $this->getRelationDao()->updateByConversationId($conversationId, ['isRead' => 1]);

        return $updatedConversation;
    }

    public function findConversationMessages($conversationId, $start, $limit)
    {
        $relations = $this->getRelationDao()->searchByConversationId($conversationId, $start, $limit);
        $messages = $this->getMessageDao()->findByIds(ArrayToolkit::column($relations, 'messageId'));
        $createUsers = $this->getUserService()->findUsersByIds(ArrayToolkit::column($messages, 'fromId'));

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
        $content = $this->biz['html_helper']->purify($content);
        $content = $this->getSensitiveService()->sensitiveCheck($content, '');
        $message = [
            'fromId' => $fromId,
            'toId' => $toId,
            'type' => $type,
            'content' => $content,
            'createdTime' => $createdTime,
        ];

        return $this->getMessageDao()->create($message);
    }

    protected function prepareConversationAndRelationForSender($message, $toId, $fromId, $createdTime)
    {
        $conversation = $this->getConversationDao()->getByFromIdAndToId($toId, $fromId);
        if ($conversation) {
            $this->getConversationDao()->update($conversation['id'], [
                'messageNum' => $conversation['messageNum'] + 1,
                'latestMessageUserId' => $message['fromId'],
                'latestMessageContent' => $message['content'],
                'latestMessageTime' => $message['createdTime'],
                'latestMessageType' => $message['type'],
            ]);
        } else {
            $conversation = [
                'fromId' => $toId,
                'toId' => $fromId,
                'messageNum' => 1,
                'latestMessageUserId' => $message['fromId'],
                'latestMessageContent' => $message['content'],
                'latestMessageTime' => $message['createdTime'],
                'latestMessageType' => $message['type'],
                'unreadNum' => 0,
                'createdTime' => $createdTime,
            ];
            $conversation = $this->getConversationDao()->create($conversation);
        }

        $relation = [
            'conversationId' => $conversation['id'],
            'messageId' => $message['id'],
            'isRead' => 0,
        ];
        $this->getRelationDao()->create($relation);
    }

    protected function prepareConversationAndRelationForReceiver($message, $fromId, $toId, $createdTime)
    {
        $conversation = $this->getConversationDao()->getByFromIdAndToId($fromId, $toId);
        if ($conversation) {
            $this->getConversationDao()->update($conversation['id'], [
                'messageNum' => $conversation['messageNum'] + 1,
                'latestMessageUserId' => $message['fromId'],
                'latestMessageContent' => $message['content'],
                'latestMessageTime' => $message['createdTime'],
                'unreadNum' => $conversation['unreadNum'] + 1,
            ]);
        } else {
            $conversation = [
                'fromId' => $fromId,
                'toId' => $toId,
                'messageNum' => 1,
                'latestMessageUserId' => $message['fromId'],
                'latestMessageContent' => $message['content'],
                'latestMessageTime' => $message['createdTime'],
                'unreadNum' => 1,
                'createdTime' => $createdTime,
            ];
            $conversation = $this->getConversationDao()->create($conversation);
        }
        $relation = [
            'conversationId' => $conversation['id'],
            'messageId' => $message['id'],
            'isRead' => 0,
        ];
        $this->getRelationDao()->create($relation);
    }

    protected function safelyUpdateConversationMessageNum($conversation)
    {
        if ($conversation['messageNum'] <= 0) {
            $this->getConversationDao()->update($conversation['id'],
                ['messageNum' => 0]);
        } else {
            $this->getConversationDao()->update($conversation['id'],
                ['messageNum' => $conversation['messageNum'] - 1]);
        }
    }

    protected function safelyUpdateConversationunreadNum($conversation)
    {
        if ($conversation['unreadNum'] <= 0) {
            $this->getConversationDao()->update($conversation['id'],
                ['unreadNum' => 0]);
        } else {
            $this->getConversationDao()->update($conversation['id'],
                ['unreadNum' => $conversation['unreadNum'] - 1]);
        }
    }

    protected function sortMessages($messages)
    {
        usort($messages, function ($a, $b) {
            if ($a['createdTime'] > $b['createdTime']) {
                return -1;
            } elseif ($a['createdTime'] == $b['createdTime']) {
                return 0;
            } else {
                return 1;
            }
        });

        return $messages;
    }

    protected function filterMessageConditions($conditions)
    {
        if (!empty($conditions['nickname'])) {
            $conditions['fromIds'] = [-1];

            $userConditions = ['nickname' => trim($conditions['nickname'])];
            $userCount = $this->getUserService()->countUsers($userConditions);
            if ($userCount) {
                $users = $this->getUserService()->searchUsers($userConditions, ['createdTime' => 'DESC'], 0, $userCount);
                $conditions['fromIds'] = ArrayToolkit::column($users, 'id');
            }
        }

        unset($conditions['nickname']);

        if (!empty($conditions['startDate'])) {
            $conditions['startDate'] = strtotime($conditions['startDate']);
        }

        if (!empty($conditions['endDate'])) {
            $conditions['endDate'] = strtotime($conditions['endDate']);
        }

        return $conditions;
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

    protected function getSensitiveService()
    {
        return $this->createService('Sensitive:SensitiveService');
    }
}
