<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Topxia\Common\Paginator;
use Topxia\WebBundle\Form\MessageType;
use Topxia\WebBundle\Form\MessageReplyType;
use Topxia\Common\ArrayToolkit;

class MessageController extends BaseController
{

    public function indexAction (Request $request)
    {
        $user = $this->getCurrentUser();
        
        $paginator = new Paginator(
            $request,
            $this->getMessageService()->getUserConversationCount($user->id),
            10
        );
        $conversations = $this->getMessageService()->findUserConversations(
            $user->id,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($conversations, 'fromId'));

        $this->getMessageService()->clearUserNewMessageCounter($user['id']);

        return $this->render('TopxiaWebBundle:Message:index.html.twig', array(
            'conversations' => $conversations,
            'users' => $users,
            'paginator' => $paginator
        ));
    }

    public function checkReceiverAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();
        $nickname = $request->query->get('value');
        $result = $this->getUserService()->isNicknameAvaliable($nickname);
        if ($result) {
            $response = array('success' => false, 'message' => '该收件人不存在');
        } else if ($currentUser['nickname'] == $nickname){
            $response = array('success' => false, 'message' => '不能给自己发私信哦！');
        } else {
            $response = array('success' => true, 'message' => '');
        }
        return $this->createJsonResponse($response);
    }

    public function showConversationAction(Request $request, $conversationId)
    {
        $user = $this->getCurrentUser();
        $conversation = $this->getMessageService()->getConversation($conversationId);
        if (empty($conversation) or $conversation['toId'] != $user['id']) {
            throw $this->createNotFoundException('私信会话不存在！');
        }
        $paginator = new Paginator(
            $request,
            $this->getMessageService()->getConversationMessageCount($conversationId),
            10
        );

        $this->getMessageService()->markConversationRead($conversationId);

        $messages = $this->getMessageService()->findConversationMessages(
            $conversation['id'], 
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $form = $this->createForm(new MessageReplyType());
        if($request->getMethod() == 'POST'){
            $form->bind($request);
            if($form->isValid()){
                $message = $form->getData();
                $message = $this->getMessageService()->sendMessage($user['id'], $conversation['fromId'], $message['content']);
                $html = $this->renderView('TopxiaWebBundle:Message:item.html.twig', array('message' => $message, 'conversation'=>$conversation));
                return $this->createJsonResponse(array('status' => 'ok', 'html' => $html));
            }
        }
        return $this->render('TopxiaWebBundle:Message:conversation-show.html.twig',array(
            'conversation'=>$conversation, 
            'messages'=>$messages, 
            'receiver'=>$this->getUserService()->getUser($conversation['fromId']),
            'form' => $form->createView(),
            'paginator' => $paginator
        ));
    }
    
    public function createAction(Request $request, $toId)
    {
        $user = $this->getCurrentUser();

        $receiver = $this->getUserService()->getUser($toId);
        $form = $this->createForm(new MessageType(), array('receiver'=>$receiver['nickname']));
        if($request->getMethod() == 'POST'){
            $form->bind($request);
            if($form->isValid()){
                $message = $form->getData();
                $nickname = $message['receiver'];
                $receiver = $this->getUserService()->getUserByNickname($nickname);
                if(empty($receiver)) {
                    throw $this->createNotFoundException("抱歉，该收信人尚未注册!");
                }
                $this->getMessageService()->sendMessage($user['id'], $receiver['id'], $message['content']);
                return $this->redirect($this->generateUrl('message'));
            }
        }
        return $this->render('TopxiaWebBundle:Message:send-message-modal.html.twig', array(
                'form' => $form->createView(),
                'userId'=>$toId));
    }

    public function sendAction(Request $request) 
    {
        $user = $this->getCurrentUser();
        $receiver = array();
        $form = $this->createForm(new MessageType());
        if($request->getMethod() == 'POST'){
            $form->bind($request);
            if($form->isValid()){
                $message = $form->getData();
                $nickname = $message['receiver'];
                $receiver = $this->getUserService()->getUserByNickname($nickname); 
                if(empty($receiver)){
                    throw $this->createNotFoundException("抱歉，该收信人尚未注册!");
                }
                $this->getMessageService()->sendMessage($user['id'], $receiver['id'], $message['content']);
            }
            return $this->redirect($this->generateUrl('message'));
        }
        return $this->render('TopxiaWebBundle:Message:create.html.twig', array(
                'form' => $form->createView()));
    }

    public function sendToAction(Request $request, $receiverId) 
    {
        $receiver = $this->getUserService()->getUser($receiverId);
        $user = $this->getCurrentUser();
        $form = $this->createForm(new MessageType(), array('receiver'=>$receiver['nickname']));
        if($request->getMethod() == 'POST'){
            $form->bind($request);
            if($form->isValid()){
                $message = $form->getData();
                $nickname = $message['receiver'];
                $receiver = $this->getUserService()->getUserByNickname($nickname); 
                if(empty($receiver)){
                    throw $this->createNotFoundException("抱歉，该收信人尚未注册!");
                }
                $this->getMessageService()->sendMessage($user['id'], $receiver['id'], $message['content']);
            }
            return $this->redirect($this->generateUrl('message'));
        }
        return $this->render('TopxiaWebBundle:Message:create.html.twig', array(
                'form' => $form->createView()));
    }


    public function deleteConversationAction(Request $request, $conversationId)
    {
        $this->getMessageService()->deleteConversation($conversationId);
        return $this->redirect($this->generateUrl('message'));
    }

    public function deleteConversationMessageAction(Request $request, $conversationId, $messageId)
    {
        $this->getMessageService()->deleteConversationMessage($conversationId, $messageId);
        $messagesCount = $this->getMessageService()->getConversationMessageCount($conversationId);
        if($messagesCount > 0){
            return $this->redirect($this->generateUrl('message_conversation_show',array('conversationId' => $conversationId)));
        }else {
           return $this->redirect($this->generateUrl('message'));
        }
    }

    public function matchAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();
        $data = array();
        $queryString = $request->query->get('q');
        $callback = $request->query->get('callback');
        $findedUsersByNickname = $this->getUserService()->searchUsers(
            array('nickname'=>$queryString),
            array('createdTime', 'DESC'),
            0,
            10);
        $findedFollowingIds = $this->getUserService()->filterFollowingIds($currentUser['id'], 
            ArrayToolkit::column($findedUsersByNickname, 'id'));

        $filterFollowingUsers = $this->getUserService()->findUsersByIds($findedFollowingIds);

        foreach ($filterFollowingUsers as $filterFollowingUser) {
            $data[] = array(
                'id' => $filterFollowingUser['id'], 
                'nickname' => $filterFollowingUser['nickname']
            );
        }

        return new JsonResponse($data);
    }

    private function getWebExtension()
    {
        return $this->container->get('topxia.twig.web_extension');
    }

    protected function getUserService(){
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getMessageService(){
        return $this->getServiceKernel()->createService('User.MessageService');
    }
}