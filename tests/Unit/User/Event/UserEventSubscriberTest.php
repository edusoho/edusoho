<?php

namespace Tests\Unit\User\Event;

use Biz\BaseTestCase;
use Biz\User\Event\UserEventSubscriber;
use Codeages\Biz\Framework\Event\Event;

class UserEventSubscriberTest extends BaseTestCase
{
    public function testGetSubscribedEvents()
    {
        $this->assertEquals([
            'user.registered' => 'onUserRegistered',
            'user.follow' => 'onUserFollowed',
            'user.unfollow' => 'onUserUnfollowed',
            'user.bind' => 'onUserBind',
            'user.unbind' => 'onUserUnbind',
            'user.change_password' => 'onUserChangePassword',
        ], UserEventSubscriber::getSubscribedEvents());
    }

    public function testOnUserBindWithoutOpenid()
    {
        $service = $this->mockBiz('WeChat:WeChatService', [
            [
                'functionName' => 'freshOfficialWeChatUserWhenLogin',
            ],
        ]);

        $event = new Event([]);
        $event->setArgument('bindType', 'weixinmob');
        $event->setArgument('bind', 'weixin');
        $event->setArgument('token', []);

        $eventSubscriber = new UserEventSubscriber($this->biz);
        $result = $eventSubscriber->onUserBind($event);

        $service->shouldNotHaveReceived('freshOfficialWeChatUserWhenLogin');
        $this->assertNull($result);
    }

    public function testOnUserBind()
    {
        $service = $this->mockBiz('WeChat:WeChatService', [
            [
                'functionName' => 'freshOfficialWeChatUserWhenLogin',
                'withParams' => [[], 'weixin', ['openid' => 1]],
            ],
        ]);

        $event = new Event([]);
        $event->setArgument('bindType', 'weixinmob');
        $event->setArgument('bind', 'weixin');
        $event->setArgument('token', ['openid' => 1]);

        $eventSubscriber = new UserEventSubscriber($this->biz);
        $eventSubscriber->onUserBind($event);

        $service->shouldHaveReceived('freshOfficialWeChatUserWhenLogin')->times(1);
    }

    public function testOnUserUnbindWithoutWeChatUser()
    {
        $service = $this->mockBiz('WeChat:WeChatService', [
            [
                'functionName' => 'getWeChatUserByTypeAndUnionId',
                'returnValue' => [],
            ],
            [
                'functionName' => 'updateWeChatUser',
            ],
        ]);
        $event = new Event([]);
        $event->setArgument('bindType', 'weixinmob');
        $event->setArgument('bind', ['fromId' => 1]);

        $eventSubscriber = new UserEventSubscriber($this->biz);
        $eventSubscriber->onUserUnbind($event);

        $service->shouldHaveReceived('getWeChatUserByTypeAndUnionId')->times(1);
        $service->shouldNotHaveReceived('updateWeChatUser');
    }

    public function testOnUserUnbind()
    {
        $service = $this->mockBiz('WeChat:WeChatService', [
            [
                'functionName' => 'getWeChatUserByTypeAndUnionId',
                'returnValue' => ['id' => 1],
            ],
            [
                'functionName' => 'updateWeChatUser',
            ],
        ]);
        $event = new Event([]);
        $event->setArgument('bindType', 'weixinmob');
        $event->setArgument('bind', ['fromId' => 1]);

        $eventSubscriber = new UserEventSubscriber($this->biz);
        $eventSubscriber->onUserUnbind($event);

        $service->shouldHaveReceived('getWeChatUserByTypeAndUnionId')->times(1);
        $service->shouldHaveReceived('updateWeChatUser')->times(1);
    }

    public function testOnUserRegisterWithoutAuth()
    {
        $systemService = $this->mockBiz('System:SettingService', [
            [
                'functionName' => 'get',
                'returnValue' => [],
            ],
        ]);
        $userService = $this->mockBiz('User:UserService', [
            [
                'functionName' => 'getUserByNickname',
            ],
        ]);
        $event = new Event([]);
        $eventSubscriber = new UserEventSubscriber($this->biz);
        $eventSubscriber->onUserRegistered($event);

        $userService->shouldNotHaveReceived('getUserByNickname');
        $systemService->shouldHaveReceived('get')->times(1);
    }

    public function testOnUserRegisterWithoutSenderUser()
    {
        $systemService = $this->mockBiz('System:SettingService', [
            [
                'functionName' => 'get',
                'withParams' => ['auth', []],
                'returnValue' => [
                    'welcome_enabled' => 'opened',
                    'welcome_sender' => 'tester',
                ],
            ],
            [
                'functionName' => 'get',
                'withParams' => ['site', []],
                'returnValue' => [],
            ],
        ]);
        $userService = $this->mockBiz('User:UserService', [
            [
                'functionName' => 'getUserByNickname',
                'returnValue' => [],
            ],
        ]);

        $event = new Event([]);
        $eventSubscriber = new UserEventSubscriber($this->biz);
        $eventSubscriber->onUserRegistered($event);

        $userService->shouldHaveReceived('getUserByNickname')->times(1);
        $systemService->shouldHaveReceived('get')->times(1);
    }

    public function testOnUserRegisterWithoutWelcomeBody()
    {
        $systemService = $this->mockBiz('System:SettingService', [
            [
                'functionName' => 'get',
                'withParams' => ['auth', []],
                'returnValue' => [
                    'welcome_enabled' => 'opened',
                    'welcome_sender' => 'tester',
                    'welcome_body' => '',
                ],
            ],
            [
                'functionName' => 'get',
                'withParams' => ['site', []],
                'returnValue' => [
                    'name' => 'site',
                    'url' => 'url',
                ],
            ],
        ]);
        $userService = $this->mockBiz('User:UserService', [
            [
                'functionName' => 'getUserByNickname',
                'returnValue' => ['id' => 1],
            ],
        ]);
        $messageService = $this->mockBiz('User:MessageService', [
            [
                'functionName' => 'sendMessage',
            ],
        ]);

        $event = new Event(['nickname' => 'admin']);
        $eventSubscriber = new UserEventSubscriber($this->biz);
        $eventSubscriber->onUserRegistered($event);

        $userService->shouldHaveReceived('getUserByNickname')->times(1);
        $systemService->shouldHaveReceived('get')->times(3);
        $messageService->shouldNotHaveReceived('sendMessage');
    }

    public function testOnUserRegistered()
    {
        $systemService = $this->mockBiz('System:SettingService', [
            [
                'functionName' => 'get',
                'withParams' => ['auth', []],
                'returnValue' => [
                    'welcome_enabled' => 'opened',
                    'welcome_sender' => 'tester',
                    'welcome_body' => 'welcome',
                ],
            ],
            [
                'functionName' => 'get',
                'withParams' => ['site', []],
                'returnValue' => [
                    'name' => 'site',
                    'url' => 'url',
                ],
            ],
        ]);
        $userService = $this->mockBiz('User:UserService', [
            [
                'functionName' => 'getUserByNickname',
                'returnValue' => ['id' => 2],
            ],
        ]);
        $messageService = $this->mockBiz('User:MessageService', [
            [
                'functionName' => 'sendMessage',
            ],
            [
                'functionName' => 'getConversationByFromIdAndToId',
                'returnValue' => ['id' => 1],
            ],
            [
                'functionName' => 'deleteConversation',
            ],
        ]);

        $event = new Event(['id' => 1, 'nickname' => 'admin']);
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
        $userService = $this->mockBiz('User:UserService', [
            [
                'functionName' => 'getUser',
                'withParams' => [2],
                'returnValue' => [
                    'id' => 2,
                    'nickname' => 'tester',
                ],
            ],
        ]);

        $notifyService = $this->mockBiz('User:NotificationService', [
            [
                'functionName' => 'notify',
            ],
        ]);

        $event = new Event(['fromId' => 2, 'toId' => 1]);
        $eventSubscriber = new UserEventSubscriber($this->biz);
        $eventSubscriber->onUserFollowed($event);

        $userService->shouldHaveReceived('getUser')->times(1);
        $notifyService->shouldHaveReceived('notify')->times(1);
    }

    public function testOnUserUnFollowed()
    {
        $userService = $this->mockBiz('User:UserService', [
            [
                'functionName' => 'getUser',
                'withParams' => [2],
                'returnValue' => [
                    'id' => 2,
                    'nickname' => 'tester',
                ],
            ],
        ]);

        $notifyService = $this->mockBiz('User:NotificationService', [
            [
                'functionName' => 'notify',
            ],
        ]);

        $event = new Event(['fromId' => 2, 'toId' => 1]);
        $eventSubscriber = new UserEventSubscriber($this->biz);
        $eventSubscriber->onUserUnfollowed($event);

        $userService->shouldHaveReceived('getUser')->times(1);
        $notifyService->shouldHaveReceived('notify')->times(1);
    }
}
