<?php

namespace AppBundle\Controller;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use Biz\User\MessageException;
use Biz\User\Service\MessageService;
use Biz\User\Service\UserService;
use Biz\User\UserException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class MessageController extends BaseController
{
    public function indexAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $paginator = new Paginator(
            $request,
            $this->getMessageService()->countUserConversations($user->id),
            10
        );
        $conversations = $this->getMessageService()->findUserConversations(
            $user->id,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($conversations, 'fromId'));

        return $this->render('message/index.html.twig', [
            'conversations' => $conversations,
            'users' => $users,
            'paginator' => $paginator,
        ]);
    }

    public function checkReceiverAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();
        $nickname = $request->query->get('value');
        $response = ['success' => true, 'message' => ''];

        if ($currentUser['nickname'] == $nickname) {
            $response = ['success' => false, 'message' => 'json_response.cannot_send_message_self.message'];

            return $this->createJsonResponse($response);
        }

        $user = $this->getUserService()->getUnDstroyedUserByNickname($nickname);
        if (empty($user)) {
            $response = ['success' => false, 'message' => 'json_response.receiver_not_exist.message'];

            return $this->createJsonResponse($response);
        }

        if (!$this->getWebExtension()->canSendMessage($user['id'])) {
            $response = ['success' => false, 'message' => 'json_response.receiver_not_allowed.message'];
        }

        return $this->createJsonResponse($response);
    }

    public function showConversationAction(Request $request, $conversationId)
    {
        $user = $this->getCurrentUser();
        $conversation = $this->getMessageService()->getConversation($conversationId);
        if (empty($conversation) || $conversation['toId'] != $user['id']) {
            $this->createNewException(MessageException::NOTFOUND_CONVERSATION());
        }
        $paginator = new Paginator(
            $request,
            $this->getMessageService()->countConversationMessages($conversationId),
            10
        );

        $this->getMessageService()->markConversationRead($conversationId);
        $this->getUserService()->updateUserNewMessageNum($user['id'], $conversation['unreadNum']);

        $messages = $this->getMessageService()->findConversationMessages(
            $conversation['id'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        if ('POST' == $request->getMethod()) {
            $message = $request->request->get('message_reply');
            if (!$this->getWebExtension()->canSendMessage($conversation['fromId'])) {
                $this->createNewException(UserException::FORBIDDEN_SEND_MESSAGE());
            }
            $message = $this->getMessageService()->sendMessage($user['id'], $conversation['fromId'], $message['content']);
            $html = $this->renderView('message/item.html.twig', ['message' => $message, 'conversation' => $conversation]);

            return $this->createJsonResponse(['status' => 'ok', 'html' => $html]);
        }

        return $this->render('message/conversation-show.html.twig', [
            'conversation' => $conversation,
            'messages' => $messages,
            'receiver' => $this->getUserService()->getUser($conversation['fromId']),
            'paginator' => $paginator,
        ]);
    }

    public function createAction(Request $request, $toId)
    {
        $user = $this->getCurrentUser();
        $receiver = $this->getUserService()->getUser($toId);
        $message = ['receiver' => $receiver['nickname']];
        if ('POST' == $request->getMethod()) {
            $message = $request->request->get('message');
            $nickname = $message['receiver'];
            $limiter = $this->getRateLimiter('message_limit', 60, 3600);
            $maxAllowance = $limiter->getAllow($user['id']);
            if (0 == $maxAllowance) {
                $this->createNewException(MessageException::MESSAGE_SEND_LIMIT());
            }
            $receiver = $this->getUserService()->getUserByNickname($nickname);
            if (empty($receiver)) {
                $this->createNewException(UserException::NOTFOUND_USER());
            }
            if (!$this->getWebExtension()->canSendMessage($receiver['id'])) {
                $this->createNewException(UserException::FORBIDDEN_SEND_MESSAGE());
            }
            $this->getMessageService()->sendMessage($user['id'], $receiver['id'], $message['content']);
            $limiter->check($user['id']);

            return $this->redirect($this->generateUrl('message'));
        }

        return $this->render('message/send-message-modal.html.twig', [
            'message' => $message,
            'userId' => $toId, ]);
    }

    public function sendAction(Request $request)
    {
        $user = $this->getCurrentUser();
        if ('POST' == $request->getMethod()) {
            $message = $request->request->get('message');
            $nickname = $message['receiver'];
            $limiter = $this->getRateLimiter('message_limit', 60, 3600);
            $maxAllowance = $limiter->getAllow($user['id']);
            if (0 == $maxAllowance) {
                $this->createNewException(MessageException::MESSAGE_SEND_LIMIT());
            }
            $receiver = $this->getUserService()->getUnDstroyedUserByNickname($nickname);
            if (empty($receiver)) {
                $this->createNewException(UserException::NOTFOUND_USER());
            }
            if (!$this->getWebExtension()->canSendMessage($receiver['id'])) {
                $this->createNewException(UserException::FORBIDDEN_SEND_MESSAGE());
            }
            $this->getMessageService()->sendMessage($user['id'], $receiver['id'], $message['content']);
            $limiter->check($user['id']);

            return $this->redirect($this->generateUrl('message'));
        }

        return $this->render('message/create.html.twig');
    }

    public function sendToAction(Request $request, $receiverId)
    {
        $receiver = $this->getUserService()->getUser($receiverId);
        $user = $this->getCurrentUser();
        $message = ['receiver' => $receiver['nickname']];
        if ('POST' == $request->getMethod()) {
            $message = $request->request->get('message');
            $nickname = $message['receiver'];
            $receiver = $this->getUserService()->getUserByNickname($nickname);
            if (empty($receiver)) {
                $this->createNewException(UserException::NOTFOUND_USER());
            }
            if (!$this->getWebExtension()->canSendMessage($receiver['id'])) {
                $this->createNewException(UserException::FORBIDDEN_SEND_MESSAGE());
            }
            $this->getMessageService()->sendMessage($user['id'], $receiver['id'], $message['content']);

            return $this->redirect($this->generateUrl('message'));
        }

        return $this->render('message/create.html.twig', ['message' => $message]);
    }

    public function deleteConversationAction(Request $request, $conversationId)
    {
        $user = $this->getCurrentUser();
        $conversation = $this->getMessageService()->getConversation($conversationId);
        if (empty($conversation) || $conversation['toId'] != $user['id']) {
            $this->createNewException(MessageException::DELETE_DENIED());
        }

        $this->getMessageService()->deleteConversation($conversationId);

        return $this->redirect($this->generateUrl('message'));
    }

    public function deleteConversationMessageAction(Request $request, $conversationId, $messageId)
    {
        $user = $this->getCurrentUser();
        $conversation = $this->getMessageService()->getConversation($conversationId);
        if (empty($conversation) || $conversation['toId'] != $user['id']) {
            $this->createNewException(MessageException::DELETE_DENIED());
        }

        $this->getMessageService()->deleteConversationMessage($conversationId, $messageId);
        $messagesCount = $this->getMessageService()->countConversationMessages($conversationId);
        if ($messagesCount > 0) {
            return $this->redirect($this->generateUrl('message_conversation_show', ['conversationId' => $conversationId]));
        } else {
            return $this->redirect($this->generateUrl('message'));
        }
    }

    public function matchAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();
        $data = [];
        $queryString = $request->query->get('q');
        $findedUsersByNickname = $this->getUserService()->searchUsers(
            ['nickname' => $queryString, 'destroyed' => 0],
            ['createdTime' => 'DESC'],
            0,
            10);
        $findedFollowingIds = $this->getUserService()->filterFollowingIds($currentUser['id'],
            ArrayToolkit::column($findedUsersByNickname, 'id'));

        $filterFollowingUsers = $this->getUserService()->findUsersByIds($findedFollowingIds);

        foreach ($filterFollowingUsers as $filterFollowingUser) {
            $data[] = [
                'id' => $filterFollowingUser['id'],
                'nickname' => $filterFollowingUser['nickname'],
            ];
        }

        return new JsonResponse($data);
    }

    protected function getWebExtension()
    {
        return $this->container->get('web.twig.extension');
    }

    protected function getRateLimiter($id, $maxAllowance, $period)
    {
        $factory = $this->getBiz()->offsetGet('ratelimiter.factory');

        return $factory($id, $maxAllowance, $period);
    }

    /**
     * @return MessageService
     */
    protected function getMessageService()
    {
        return $this->getBiz()->service('User:MessageService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }
}
