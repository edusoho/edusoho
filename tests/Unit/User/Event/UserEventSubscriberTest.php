<?php

namespace Tests\Unit\User\Event;

use Biz\BaseTestCase;
use Biz\User\Event\UserEventSubscriber;
use Codeages\Biz\Framework\Event\Event;

class UserEventSubscriberTest extends BaseTestCase
{
    public function testGetSubscribedEvents()
    {
        $this->assertEquals(array(
            'user.registered' => 'onUserRegistered',
            'user.follow' => 'onUserFollowed',
            'user.unfollow' => 'onUserUnfollowed',
            'user.bind' => 'onUserBind',
            'user.unbind' => 'onUserUnbind',
        ), UserEventSubscriber::getSubscribedEvents());
    }

    public function testOnUserBindWithoutOpenid()
    {
        $service = $this->mockBiz('WeChat:WeChatService', array(
            array(
                'functionName' => 'freshOfficialWeChatUserWhenLogin',
            ),
        ));

        $event = new Event(array());
        $event->setArgument('bindType', 'weixinmob');
        $event->setArgument('bind', 'weixin');
        $event->setArgument('token', array());

        $eventSubscriber = new UserEventSubscriber($this->biz);
        $result = $eventSubscriber->onUserBind($event);

        $service->shouldNotHaveReceived('freshOfficialWeChatUserWhenLogin');
        $this->assertNull($result);
    }

    public function testOnUserBind()
    {
        $service = $this->mockBiz('WeChat:WeChatService', array(
            array(
                'functionName' => 'freshOfficialWeChatUserWhenLogin',
                'withParams' => array(array(), 'weixin', array('openid' => 1)),
            ),
        ));

        $event = new Event(array());
        $event->setArgument('bindType', 'weixinmob');
        $event->setArgument('bind', 'weixin');
        $event->setArgument('token', array('openid' => 1));

        $eventSubscriber = new UserEventSubscriber($this->biz);
        $eventSubscriber->onUserBind($event);

        $service->shouldHaveReceived('freshOfficialWeChatUserWhenLogin')->times(1);
    }

    public function testOnUserUnbindWithoutWeChatUser()
    {
        $service = $this->mockBiz('WeChat:WeChatService', array(
            array(
                'functionName' => 'getWeChatUserByTypeAndUnionId',
                'returnValue' => array(),
            ),
            array(
                'functionName' => 'updateWeChatUser',
            ),
        ));
        $event = new Event(array());
        $event->setArgument('bindType', 'weixinmob');
        $event->setArgument('bind', array('fromId' => 1));

        $eventSubscriber = new UserEventSubscriber($this->biz);
        $eventSubscriber->onUserUnbind($event);

        $service->shouldHaveReceived('getWeChatUserByTypeAndUnionId')->times(1);
        $service->shouldNotHaveReceived('updateWeChatUser');
    }

    public function testOnUserUnbind()
    {
        $service = $this->mockBiz('WeChat:WeChatService', array(
            array(
                'functionName' => 'getWeChatUserByTypeAndUnionId',
                'returnValue' => array('id' => 1),
            ),
            array(
                'functionName' => 'updateWeChatUser',
            ),
        ));
        $event = new Event(array());
        $event->setArgument('bindType', 'weixinmob');
        $event->setArgument('bind', array('fromId' => 1));

        $eventSubscriber = new UserEventSubscriber($this->biz);
        $eventSubscriber->onUserUnbind($event);

        $service->shouldHaveReceived('getWeChatUserByTypeAndUnionId')->times(1);
        $service->shouldHaveReceived('updateWeChatUser')->times(1);
    }

    public function testOnUserRegisterWithoutAuth()
    {
        $systemService = $this->mockBiz('System:SettingService', array(
            array(
                'functionName' => 'get',
                'returnValue' => array(),
            ),
        ));
        $userService = $this->mockBiz('User:UserService', array(
            array(
                'functionName' => 'getUserByNickname',
            ),
        ));
        $event = new Event(array());
        $eventSubscriber = new UserEventSubscriber($this->biz);
        $eventSubscriber->onUserRegistered($event);

        $userService->shouldNotHaveReceived('getUserByNickname');
        $systemService->shouldHaveReceived('get')->times(1);
    }

    public function testOnUserRegisterWithoutSenderUser()
    {
        $systemService = $this->mockBiz('System:SettingService', array(
            array(
                'functionName' => 'get',
                'withParams' => array('auth', array()),
                'returnValue' => array(
                    'welcome_enabled' => 'opened',
                    'welcome_sender' => 'tester',
                ),
            ),
            array(
                'functionName' => 'get',
                'withParams' => array('site', array()),
                'returnValue' => array(),
            ),
        ));
        $userService = $this->mockBiz('User:UserService', array(
            array(
                'functionName' => 'getUserByNickname',
                'returnValue' => array(),
            ),
        ));

        $event = new Event(array());
        $eventSubscriber = new UserEventSubscriber($this->biz);
        $eventSubscriber->onUserRegistered($event);

        $userService->shouldHaveReceived('getUserByNickname')->times(1);
        $systemService->shouldHaveReceived('get')->times(1);
    }

    public function testOnUserRegisterWithoutWelcomeBody()
    {
        $systemService = $this->mockBiz('System:SettingService', array(
            array(
                'functionName' => 'get',
                'withParams' => array('auth', array()),
                'returnValue' => array(
                    'welcome_enabled' => 'opened',
                    'welcome_sender' => 'tester',
                    'welcome_body' => '',
                ),
            ),
            array(
                'functionName' => 'get',
                'withParams' => array('site', array()),
                'returnValue' => array(
                    'name' => 'site',
                    'url' => 'url',
                ),
            ),
        ));
        $userService = $this->mockBiz('User:UserService', array(
            array(
                'functionName' => 'getUserByNickname',
                'returnValue' => array('id' => 1),
            ),
        ));
        $messageService = $this->mockBiz('User:MessageService', array(
            array(
                'functionName' => 'sendMessage',
            ),
        ));

        $event = new Event(array('nickname' => 'admin'));
        $eventSubscriber = new UserEventSubscriber($this->biz);
        $eventSubscriber->onUserRegistered($event);

        $userService->shouldHaveReceived('getUserByNickname')->times(1);
        $systemService->shouldHaveReceived('get')->times(3);
        $messageService->shouldNotHaveReceived('sendMessage');
    }

    public function testOnUserRegistered()
    {
        $systemService = $this->mockBiz('System:SettingService', array(
            array(
                'functionName' => 'get',
                'withParams' => array('auth', array()),
                'returnValue' => array(
                    'welcome_enabled' => 'opened',
                    'welcome_sender' => 'tester',
                    'welcome_body' => 'welcome',
                ),
            ),
            array(
                'functionName' => 'get',
                'withParams' => array('site', array()),
                'returnValue' => array(
                    'name' => 'site',
                    'url' => 'url',
                ),
            ),
        ));
        $userService = $this->mockBiz('User:UserService', array(
            array(
                'functionName' => 'getUserByNickname',
                'returnValue' => array('id' => 2),
            ),
        ));
        $messageService = $this->mockBiz('User:MessageService', array(
            array(
                'functionName' => 'sendMessage',
            ),
            array(
                'functionName' => 'getConversationByFromIdAndToId',
                'returnValue' => array('id' => 1),
            ),
            array(
                'functionName' => 'deleteConversation',
            ),
        ));

        $event = new Event(array('id' => 1, 'nickname' => 'admin'));
        $eventSubscriber = new UserEventSubscriber($this->biz);
        $eventSubscriber->onUserRegistered($event);

        $userService->shouldHaveReceived('getUserByNickname')->times(1);
        $systemService->shouldHaveReceived('get')->times(3);
        $messageService->shouldHaveReceived('sendMessage')->times(1);
        $messageService->shouldHaveReceived('getConversationByFromIdAndToId')->times(1);
        $messageService->shouldHaveReceived('deleteConversation')->times(1);
    }

    public function testOnUserFollowed()
    {
        $userService = $this->mockBiz('User:UserService', array(
            array(
                'functionName' => 'getUser',
                'withParams' => array(2),
                'returnValue' => array(
                    'id' => 2,
                    'nickname' => 'tester',
                ),
            ),
        ));

        $notifyService = $this->mockBiz('User:NotificationService', array(
            array(
                'functionName' => 'notify',
            ),
        ));

        $event = new Event(array('fromId' => 2, 'toId' => 1));
        $eventSubscriber = new UserEventSubscriber($this->biz);
        $eventSubscriber->onUserFollowed($event);

        $userService->shouldHaveReceived('getUser')->times(1);
        $notifyService->shouldHaveReceived('notify')->times(1);
    }

    public function testOnUserUnFollowed()
    {
        $userService = $this->mockBiz('User:UserService', array(
            array(
                'functionName' => 'getUser',
                'withParams' => array(2),
                'returnValue' => array(
                    'id' => 2,
                    'nickname' => 'tester',
                ),
            ),
        ));

        $notifyService = $this->mockBiz('User:NotificationService', array(
            array(
                'functionName' => 'notify',
            ),
        ));

        $event = new Event(array('fromId' => 2, 'toId' => 1));
        $eventSubscriber = new UserEventSubscriber($this->biz);
        $eventSubscriber->onUserUnfollowed($event);

        $userService->shouldHaveReceived('getUser')->times(1);
        $notifyService->shouldHaveReceived('notify')->times(1);
    }
}
