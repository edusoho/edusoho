<?php

namespace Tests\Unit\User\Service;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\ReflectionUtils;
use Biz\BaseTestCase;
use Biz\User\Service\MessageService;
use Biz\User\Service\UserService;

class MessageServiceTest extends BaseTestCase
{
    public function testCountMessage()
    {
        /*create*/
        $sender = $this->createSender();
        $receiver = $this->createReceiver();
        $content = 'testSendMessage';
        for ($i = 0; $i < 4; ++$i) {
            $this->getMessageService()->sendMessage($sender['id'], $receiver['id'], $i.$content.$i);
        }

        $result = $this->getMessageService()->countMessages(array());

        $this->assertEquals(4, $result);
    }

    public function testSearchMessage()
    {
        /*create*/
        $sender = $this->createSender();
        $receiver = $this->createReceiver();
        $content = 'testSendMessage';
        for ($i = 0; $i < 4; ++$i) {
            $this->getMessageService()->sendMessage($sender['id'], $receiver['id'], $i.$content.$i);
        }

        $results = $this->getMessageService()->searchMessages(array(), array(), 0, 10);

        $this->assertEquals(4, count($results));
    }

    public function testDeleteMessagesByAdmin()
    {
        /*create*/
        $sender = $this->createSender();
        $receiver = $this->createReceiver();
        $content = 'testSendMessage';
        for ($i = 0; $i < 4; ++$i) {
            $this->getMessageService()->sendMessage($sender['id'], $receiver['id'], $i.$content.$i);
        }

        $conversation = $this->getMessageService()->getConversationByFromIdAndToId($receiver['id'], $sender['id']);
        $messageByConversation = $this->getMessageService()->findConversationMessages($conversation['id'], 0, 10);
        $this->getMessageService()->deleteMessagesByIds(ArrayToolkit::column($messageByConversation, 'id'));

        $result = $this->getMessageService()->countMessages(array('isDelete' => 1));

        $this->assertEquals(4, $result);
    }

    public function testSendMessage()
    {
        /*create*/
        $sender = $this->createSender();
        $receiver = $this->createReceiver();
        $content = 'testSendMessage';
        for ($i = 0; $i < 4; ++$i) {
            $this->getMessageService()->sendMessage($sender['id'], $receiver['id'], $i.$content.$i);
        }

        $conversationToSender = $this->getMessageService()->getConversationByFromIdAndToId($receiver['id'], $sender['id']);
        $messagesByConversationToSender = $this->getMessageService()->findConversationMessages($conversationToSender['id'], 0, 10);

        /* test conversation */

        $this->assertEquals($conversationToSender['latestMessageUserId'], $sender['id']);
        $this->assertEquals($conversationToSender['toId'], $sender['id']);
        $this->assertEquals($conversationToSender['fromId'], $receiver['id']);
        $this->assertEquals($conversationToSender['messageNum'], 4);
        $this->assertEquals($conversationToSender['unreadNum'], 0);
        $this->assertEquals($conversationToSender['latestMessageContent'], '3testSendMessage3');

        /* test message */
        foreach ($messagesByConversationToSender as $messageByConversationToSender) {
            $this->assertEquals($messageByConversationToSender['fromId'], $sender['id']);
            $this->assertEquals($messageByConversationToSender['toId'], $receiver['id']);
        }

        $conversationToReceiver = $this->getMessageService()->getConversationByFromIdAndToId($sender['id'], $receiver['id']);
        $messagesByConversationToReceiver = $this->getMessageService()->findConversationMessages($conversationToReceiver['id'], 0, 10);

        /* test conversation */

        $this->assertEquals($conversationToReceiver['latestMessageUserId'], $sender['id']);
        $this->assertEquals($conversationToReceiver['toId'], $receiver['id']);
        $this->assertEquals($conversationToReceiver['fromId'], $sender['id']);
        $this->assertEquals($conversationToReceiver['messageNum'], 4);
        $this->assertEquals($conversationToReceiver['unreadNum'], 4);
        $this->assertEquals($conversationToReceiver['latestMessageContent'], '3testSendMessage3');

        /* test message */
        foreach ($messagesByConversationToReceiver as $messageByConversationToReceiver) {
            $this->assertEquals($messageByConversationToReceiver['fromId'], $sender['id']);
            $this->assertEquals($messageByConversationToReceiver['toId'], $receiver['id']);
        }
    }

    /**
     * @expectedException \Biz\User\MessageException
     */
    public function testSendMessageWithEmptyFromIdAndToId()
    {
        $content = 'testSendMessage';
        $this->getMessageService()->sendMessage(0, 0, $content);
    }

    /**
     * @expectedException \Biz\User\MessageException
     */
    public function testSendMessageWithEqualFromIdAndToId()
    {
        /*create*/
        $sender = $this->createSender();
        $content = 'testSendMessage';

        $this->getMessageService()->sendMessage($sender['id'], $sender['id'], $content);

        $this->getMessageService()->sendMessage(0, 0, $content);
    }

    /**
     * @expectedException \Biz\User\MessageException
     */
    public function testSendMessageWithEmptyContent()
    {
        /*create*/
        $sender = $this->createSender();
        $receiver = $this->createReceiver();
        $content = '';

        $this->getMessageService()->sendMessage($sender['id'], $receiver['id'], $content);

        $this->getMessageService()->sendMessage(0, 0, $content);
    }

    public function testDeleteMessagesByIds()
    {
        /*create*/
        $sender = $this->createSender();
        $receiver = $this->createReceiver();
        $content = 'testSendMessage';
        for ($i = 0; $i < 4; ++$i) {
            $this->getMessageService()->sendMessage($sender['id'], $receiver['id'], $i.$content.$i);
        }

        $conversation = $this->getMessageService()->getConversationByFromIdAndToId($receiver['id'], $sender['id']);
        $messageByConversation = $this->getMessageService()->findConversationMessages($conversation['id'], 0, 10);
        $this->getMessageService()->deleteMessagesByIds(ArrayToolkit::column($messageByConversation, 'id'));

        $result = $this->getMessageService()->countMessages(array('isDelete' => 1));

        $this->assertEquals(4, $result);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     */
    public function testDeleteMessagesByIdsWithEmptyException()
    {
        $this->getMessageService()->deleteMessagesByIds();
    }

    public function testFindUserConversations()
    {
        /*create*/
        $sender = $this->createSender();
        $receiver = $this->createReceiver();
        $content = 'testSendMessage';
        $this->getMessageService()->sendMessage($sender['id'], $receiver['id'], $content);

        $results = $this->getMessageService()->findUserConversations($receiver['id'], 0, 10);

        $this->assertEquals(1, count($results));
    }

    public function testCountUserConversations()
    {
        /*create*/
        $sender = $this->createSender();
        $receiver = $this->createReceiver();
        $content = 'testSendMessage';
        $this->getMessageService()->sendMessage($sender['id'], $receiver['id'], $content);

        $result = $this->getMessageService()->countUserConversations($receiver['id']);

        $this->assertEquals(1, $result);
    }

    public function testClearUserNewMessageCounter()
    {
        /*create*/
        $sender = $this->createSender();
        $receiver = $this->createReceiver();
        $content = 'testSendMessage';
        $this->getMessageService()->sendMessage($sender['id'], $receiver['id'], $content);

        $beforeUser = $this->getUserService()->getUser($receiver['id']);
        $this->assertEquals(1, $beforeUser['newMessageNum']);

        $this->getMessageService()->clearUserNewMessageCounter($receiver['id']);

        $afterUser = $this->getUserService()->getUser($receiver['id']);
        $this->assertEquals(0, $afterUser['newMessageNum']);
    }

    public function testFilterMessageConditions()
    {
        $conditions = ReflectionUtils::invokeMethod($this->getMessageService(), 'filterMessageConditions', array(
            array(
                'nickname' => 'admin',
                'startDate' => 'now',
                'endDate' => 'now',
            ),
        ));
        $this->assertNotEmpty($conditions['fromIds']);
        $this->assertNotEquals('now', $conditions['startDate']);
        $this->assertNotEmpty($conditions['startDate']);

        $this->assertNotEquals('now', $conditions['endDate']);
        $this->assertNotEmpty($conditions['endDate']);
    }

    public function testShowConversation()
    {
        $sender = $this->createSender();
        $receiver = $this->createReceiver();
        $content = 'testSendMessage';
        for ($i = 0; $i < 4; ++$i) {
            $this->getMessageService()->sendMessage($sender['id'], $receiver['id'], $i.$content.$i);
        }

        /* get conversationToSender*/
        $conversationToSender = $this->getMessageService()->
            getConversationByFromIdAndToId($receiver['id'], $sender['id']);
        $updatedConversation = $this->getMessageService()->markConversationRead($conversationToSender['id']);

        /*测试conversation*/
        $this->assertEquals($updatedConversation['messageNum'], 4);
        $this->assertEquals($updatedConversation['unreadNum'], 0);
        $this->assertEquals($updatedConversation['fromId'], $receiver['id']);
        $this->assertEquals($updatedConversation['toId'], $sender['id']);
        $this->assertEquals($updatedConversation['latestMessageUserId'], $sender['id']);
        $this->assertEquals($updatedConversation['latestMessageContent'], '3testSendMessage3');

        /*get conversationToReceiver*/
        $conversationToReceiver = $this->getMessageService()->
            getConversationByFromIdAndToId($sender['id'], $receiver['id']);
        $updatedConversation = $this->getMessageService()->markConversationRead($conversationToReceiver['id']);

        /*测试conversation*/
        $this->assertEquals($updatedConversation['messageNum'], 4);
        $this->assertEquals($updatedConversation['unreadNum'], 0);
        $this->assertEquals($updatedConversation['toId'], $receiver['id']);
        $this->assertEquals($updatedConversation['fromId'], $sender['id']);
        $this->assertEquals($updatedConversation['latestMessageUserId'], $sender['id']);
        $this->assertEquals($updatedConversation['latestMessageContent'], '3testSendMessage3');
    }

    /**
     * @group current
     */
    public function testDeleteMessageBySenderUser()
    {
        $sender = $this->createSender();
        $receiver = $this->createReceiver();
        $content = 'testSendMessage';
        for ($i = 0; $i < 4; ++$i) {
            $this->getMessageService()->sendMessage($sender['id'], $receiver['id'], $i.$content.$i);
        }
        $conversationToSender = $this->getMessageService()->
            getConversationByFromIdAndToId($receiver['id'], $sender['id']);
        $messagesCount = $this->getMessageService()->countConversationMessages($conversationToSender['id']);
        $messagesOfConversation = $this->getMessageService()->findConversationMessages($conversationToSender['id'], 0, $messagesCount);
        $updatedConversation = $this->getMessageService()->getConversation($conversationToSender['id']);
        /* test conversation */
        foreach ($messagesOfConversation as $messageOfConversation) {
            $this->getMessageService()->deleteConversationMessage($conversationToSender['id'], $messageOfConversation['id']);
            $this->assertEquals($updatedConversation['unreadNum'], 0);
            $this->assertEquals($updatedConversation['fromId'], $receiver['id']);
            $this->assertEquals($updatedConversation['toId'], $sender['id']);
            $this->assertEquals($updatedConversation['latestMessageUserId'], $sender['id']);
            $this->assertEquals($updatedConversation['latestMessageContent'], '3testSendMessage3');
        }
        $updatedConversation = $this->getMessageService()->getConversation($conversationToSender['id']);
        $this->assertEquals($updatedConversation['messageNum'], 0);
        $this->assertEquals($updatedConversation['unreadNum'], 0);

        /* test relation count*/
        $messagesCount = $this->getMessageService()->countConversationMessages($conversationToSender['id']);
        $this->assertEquals($messagesCount, 0);
    }

    public function testDeleteMessageByReceiverUser()
    {
        $sender = $this->createSender();
        $receiver = $this->createReceiver();
        $content = 'testSendMessage';
        for ($i = 0; $i < 4; ++$i) {
            $this->getMessageService()->sendMessage($sender['id'], $receiver['id'], $i.$content.$i);
        }
        $conversationToReceiver = $this->getMessageService()->
            getConversationByFromIdAndToId($sender['id'], $receiver['id']);
        $messagesCount = $this->getMessageService()->countConversationMessages($conversationToReceiver['id']);
        $messagesOfConversation = $this->getMessageService()->findConversationMessages($conversationToReceiver['id'], 0, $messagesCount);
        $updatedConversation = $this->getMessageService()->getConversation($conversationToReceiver['id']);

        /* test conversation */
        foreach ($messagesOfConversation as $messageOfConversation) {
            $this->getMessageService()->deleteConversationMessage($conversationToReceiver['id'], $messageOfConversation['id']);
            $this->assertEquals($updatedConversation['fromId'], $sender['id']);
            $this->assertEquals($updatedConversation['toId'], $receiver['id']);
            $this->assertEquals($updatedConversation['latestMessageUserId'], $sender['id']);
            $this->assertEquals($updatedConversation['latestMessageContent'], '3testSendMessage3');
        }
        $updatedConversation = $this->getMessageService()->getConversation($conversationToReceiver['id']);
        $this->assertEquals($updatedConversation['messageNum'], 0);
        $this->assertEquals($updatedConversation['unreadNum'], 0);

        /* test relation count*/
        $messagesCount = $this->getMessageService()->countConversationMessages($conversationToReceiver['id']);
        $this->assertEquals($messagesCount, 0);
    }

    public function testDeleteConversationByUser()
    {
        $sender = $this->createSender();
        $receiver = $this->createReceiver();
        $content = 'testSendMessage';
        for ($i = 0; $i < 4; ++$i) {
            $this->getMessageService()->sendMessage($sender['id'], $receiver['id'], $i.$content.$i);
        }
        /*  for sender */
        $conversationToSender = $this->getMessageService()->
            getConversationByFromIdAndToId($receiver['id'], $sender['id']);
        $this->getMessageService()->deleteConversation($conversationToSender['id']);
        $messagesCount = $this->getMessageService()->countConversationMessages($conversationToSender['id']);
        $this->assertEmpty($messagesCount);
        $conversationToSender = $this->getMessageService()->getConversation($conversationToSender['id']);
        $this->assertEmpty($conversationToSender);

        /* for receiver */
        $conversationToReceiver = $this->getMessageService()->
            getConversationByFromIdAndToId($sender['id'], $receiver['id']);
        $this->getMessageService()->deleteConversation($conversationToReceiver['id']);
        $conversationToReceiver = $this->getMessageService()->getConversation($conversationToReceiver['id']);
        $this->assertEmpty($conversationToReceiver);
        $messagesCount = $this->getMessageService()->countConversationMessages($conversationToReceiver['id']);
        $this->assertEmpty($messagesCount);
    }

    public function testFindNewUserConversations()
    {
        $this->mockBiz(
            'User:MessageConversationDao',
            array(
                array(
                    'functionName' => 'search',
                    'returnValue' => array(array('id' => 111, 'toId' => 2, 'unreadNum' => 1)),
                    'withParams' => array(
                        array('toId' => 2, 'lessUnreadNum' => 0),
                        array('latestMessageTime' => 'DESC'),
                        0,
                        5,
                    ),
                ),
            )
        );
        $result = $this->getMessageService()->findNewUserConversations(2, 0, 5);
        $this->assertEquals(array(array('id' => 111, 'toId' => 2, 'unreadNum' => 1)), $result);
    }

    protected function createSender()
    {
        $sender = array();
        $sender['email'] = 'sender@sender.com';
        $sender['nickname'] = 'sender';
        $sender['password'] = 'sender';

        return $this->getUserService()->register($sender);
    }

    protected function createReceiver()
    {
        $receiver = array();
        $receiver['email'] = 'receiver@receiver.com';
        $receiver['nickname'] = 'receiver';
        $receiver['password'] = 'receiver';

        return $this->getUserService()->register($receiver);
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return MessageService
     */
    protected function getMessageService()
    {
        return $this->createService('User:MessageService');
    }
}
