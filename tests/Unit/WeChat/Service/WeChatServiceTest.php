<?php

namespace Tests\Unit\WeChat\Service;

use AppBundle\Common\ReflectionUtils;
use Biz\BaseTestCase;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\System\Service\SettingService;
use Biz\WeChat\Dao\SubscribeRecordDao;
use Biz\WeChat\Service\WeChatService;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;

class WeChatServiceTest extends BaseTestCase
{
    /**
     * @expectedException \Biz\Common\CommonException
     * @expectedExceptionMessage exception.common_parameter_missing
     */
    public function testCreateWeChatUserWithErrorParams()
    {
        $this->getWeChatService()->createWeChatUser([]);
    }

    public function testFindAllBindUser()
    {
        $result = $this->getWeChatService()->findAllBindUserIds();

        $this->assertEquals([], $result);
    }

    public function testGetWeChatUserByTypeAndUnionId()
    {
        $this->mockCreateWeChatUser(['unionId' => 'qqq']);
        $result = $this->getWeChatService()->getWeChatUserByTypeAndUnionId('official', 'qqq');
        $this->assertEquals('qqq', $result['unionId']);

        $result = $this->getWeChatService()->getWeChatUserByTypeAndUnionId('open-app', 'qqq');
        $this->assertEmpty($result);
    }

    public function testGetWeChatUserByTypeAndOpenId()
    {
        $this->mockCreateWeChatUser(['openId' => 'www']);
        $result = $this->getWeChatService()->getWeChatUserByTypeAndOpenId('official', 'www');
        $this->assertEquals('www', $result['openId']);

        $result = $this->getWeChatService()->getWeChatUserByTypeAndOpenId('open-app', 'www');
        $this->assertEmpty($result);
    }

    public function testFindWeChatUsersByUserId()
    {
        $this->mockCreateWeChatUser(['userId' => 2]);
        $result = $this->getWeChatService()->findWeChatUsersByUserId(2);
        $this->assertEquals(2, $result[0]['userId']);
    }

    public function testFindSubscribedUsersByUserIdsAndType()
    {
        $result = $this->getWeChatService()->findSubscribedUsersByUserIdsAndType([1], 'open-app');
        $this->assertEmpty($result);
    }

    public function testFindWeChatUsersByUserIdAndType()
    {
        $this->mockCreateWeChatUser(['userId' => 2]);
        $result = $this->getWeChatService()->findWeChatUsersByUserIdAndType(2, 'official');
        $this->assertEquals(2, $result[0]['userId']);
    }

    public function testGetOfficialWeChatUserByUserId()
    {
        $result = $this->getWeChatService()->getOfficialWeChatUserByUserId(2);
        $this->assertEmpty($result);

        $this->mockCreateWeChatUser(['userId' => 2, 'lastRefreshTime' => time() + 86400]);
        $result = $this->getWeChatService()->getOfficialWeChatUserByUserId(2);
        $this->assertEquals(2, $result['userId']);
    }

    public function testUpdateWeChatUser()
    {
        $weChatUser = $this->mockCreateWeChatUser(['userId' => 2]);
        $this->getWeChatService()->updateWeChatUser($weChatUser['id'], ['userId' => 3]);

        $result = $this->getWeChatService()->getWeChatUser($weChatUser['id']);
        $this->assertEquals(3, $result['userId']);
    }

    /**
     * @expectedException \Biz\User\UserException
     * @expectedExceptionMessage exception.user.not_found
     */
    public function testUpdateWeChatUserWithExistUser()
    {
        $this->getWeChatService()->updateWeChatUser(1, []);
    }

    public function testSearchWeChatUsers()
    {
        $this->mockCreateWeChatUser(['openId' => 'www']);

        $result = $this->getWeChatService()->searchWeChatUsers(['userId' => 1], ['lastRefreshTime' => 'ASC'], 0, 10, ['id', 'openId', 'unionId', 'userId']);

        $this->assertEquals('www', $result[0]['openId']);
    }

    public function testBatchSyncOfficialWeChatUsers()
    {
        $biz = $this->getBiz();
        $mockClient = \Mockery::mock('AppBundle\Component\Notification\WeChatTemplateMessage\Client');
        $mockClient->shouldReceive('getUserList')->andReturn([
            'data' => ['openid' => []],
            'next_openid' => 'thisisafakenextopenid',
        ]);

        $biz['wechat.template_message_client'] = $mockClient;

        $result = $this->getWeChatService()->batchSyncOfficialWeChatUsers();
        $this->assertEquals(['next_openid' => 'thisisafakenextopenid'], $result);
    }

    public function testBatchSyncOfficialWeChatUsersWithoutNewOpenIds()
    {
        $biz = $this->getBiz();
        $mockClient = \Mockery::mock('AppBundle\Component\Notification\WeChatTemplateMessage\Client');

        $mockClient->shouldReceive('getUserList')->andReturn([
            'data' => ['openid' => [1, 2, 3]],
            'next_openid' => 'thisisafakenextopenid',
        ]);

        $biz['wechat.template_message_client'] = $mockClient;

        $this->mockBiz(
            'WeChat:UserWeChatDao',
            [
                [
                    'functionName' => 'findOpenIdsInListsByType',
                    'returnValue' => [
                        ['openId' => 1],
                        ['openId' => 2],
                        ['openId' => 3],
                    ],
                ],
            ]
        );

        $result = $this->getWeChatService()->batchSyncOfficialWeChatUsers();
        $this->assertEmpty($result);
    }

    public function testBatchSyncOfficialWeChatUsersWithNewOpenIds()
    {
        $biz = $this->getBiz();
        $mockClient = \Mockery::mock('AppBundle\Component\Notification\WeChatTemplateMessage\Client');

        $mockClient->shouldReceive('getUserList')->andReturn([
            'data' => ['openid' => [1, 2, 3]],
            'next_openid' => 'thisisafakenextopenid',
        ]);

        $mockClient->shouldReceive('getAppId')->andReturn('thisisappid');

        $biz['wechat.template_message_client'] = $mockClient;

        $this->mockBiz(
            'WeChat:UserWeChatDao',
            [
                [
                    'functionName' => 'findOpenIdsInListsByType',
                    'returnValue' => [
                        ['openId' => 1],
                        ['openId' => 2],
                    ],
                ],
                [
                    'functionName' => 'batchCreate',
                ],
            ]
        );

        $result = $this->getWeChatService()->batchSyncOfficialWeChatUsers();
        $this->assertEquals(['next_openid' => 'thisisafakenextopenid'], $result);
    }

    public function testFreshOfficialWeChatUserWhenLogin()
    {
        $weChatUser = $this->mockCreateWeChatUser(['openId' => 'www', 'userId' => 2]);
        $this->getWeChatService()->freshOfficialWeChatUserWhenLogin(['id' => 10], [], ['openid' => 'www']);

        $result = $this->getWeChatService()->getWeChatUser($weChatUser['id']);
        $this->assertEquals(10, $result['userId']);

        $this->getWeChatService()->freshOfficialWeChatUserWhenLogin(['id' => 9], ['fromId' => 'ppp'], ['openid' => 'uuu']);
        $result = $this->getWeChatService()->getWeChatUserByTypeAndOpenId('official', 'uuu');
        $this->assertEquals(9, $result['userId']);
    }

    public function testBatchFreshOfficialWeChatUsers()
    {
        $weChatUser1 = $this->mockCreateWeChatUser(['userId' => 3]);
        $weChatUser2 = $this->mockCreateWeChatUser(['userId' => 4, 'unionId' => 'thisisunionid', 'openId' => 'thisisopenid']);

        $freshWeChatUser1 = [
            'openid' => 'ffffffffffffffffffff',
            'unionid' => 'hhhhhhhhhhhhhhhhhhhh',
            'subscribe' => 0,
        ];

        $freshWeChatUser2 = [
            'openid' => 'thisisopenid',
            'unionid' => 'thisisunionid',
            'subscribe' => 1,
        ];

        $mockClient = \Mockery::mock('AppBundle\Component\Notification\WeChatTemplateMessage\Client');
        $mockClient->shouldReceive('batchGetUserInfo')->andReturn([
            $freshWeChatUser1,
            $freshWeChatUser2,
        ]);

        $this->mockBiz(
            'User:UserService',
            [
                [
                    'functionName' => 'findUserBindByTypeAndToIds',
                    'returnValue' => [
                        [
                            'fromId' => 'hhhhhhhhhhhhhhhhhhhh',
                            'toId' => 3,
                        ],
                        [
                            'fromId' => 'thisisunionid',
                            'toId' => 4,
                        ],
                    ],
                ],
                [
                    'functionName' => 'findUserBindByTypeAndFromIds',
                    'returnValue' => [
                        [
                            'fromId' => 'hhhhhhhhhhhhhhhhhhhh',
                            'toId' => 3,
                        ],
                        [
                            'fromId' => 'thisisunionid',
                            'toId' => 4,
                        ],
                    ],
                ],
            ]
        );

        $biz = $this->getBiz();
        $biz['wechat.template_message_client'] = $mockClient;
        $this->getWeChatService()->batchFreshOfficialWeChatUsers([$weChatUser1, $weChatUser2]);

        $result1 = $this->getWeChatService()->getOfficialWeChatUserByUserId(3);
        $result2 = $this->getWeChatService()->getOfficialWeChatUserByUserId(4);

        $this->assertEquals($freshWeChatUser1, $result1['data']);
        $this->assertEquals($freshWeChatUser2, $result2['data']);
    }

    public function testGetTemplateIdWithUnabledSetting()
    {
        $wechatSetting = [
            'wechat_notification_enabled' => 0,
        ];
        $this->getSettingService()->set('wechat', $wechatSetting);
        $result = $this->getWeChatService()->getTemplateId(1);
        $this->assertEmpty($result);
    }

    public function testGetTemplateIdWithEmptyTemplateId()
    {
        $wechatSetting = [
            'wechat_notification_enabled' => 1,
            1 => [
                'status' => 'open',
                'templateId' => null,
            ],
        ];
        $this->getSettingService()->set('wechat', $wechatSetting);
        $result = $this->getWeChatService()->getTemplateId(1);
        $this->assertEmpty($result);
    }

    public function testGetTemplateId()
    {
        $wechatSetting = [
            'wechat_notification_enabled' => 1,
            'templates' => [
                'abc' => [
                    'status' => 'open',
                    'templateId' => 123,
                ],
            ],
        ];

        $notificationSetting = [
            'is_authorization' => 1,
            'notification_type' => 'serviceFollow',
        ];
        $this->getSettingService()->set('wechat', $wechatSetting);
        $this->getSettingService()->set('wechat_notification', $notificationSetting);
        $result = $this->getWeChatService()->getTemplateId('abc');
        $this->assertEquals(123, $result);
    }

    public function testHandleCloudNotificationWithSameSetting()
    {
        $setting = [
            'wechat_notification_enabled' => 1,
        ];

        $result = $this->getWeChatService()->handleCloudNotification($setting, $setting, []);
        $this->assertTrue($result);

        $mockApiClient = \Mockery::mock('Biz\CloudPlatform\Client\CloudAPI');
        $mockApiClient->shouldReceive('get')->andThrow('\RuntimeException');
        ReflectionUtils::setStaticProperty(new CloudAPIFactory(), 'api', $mockApiClient);

        $result = $this->getWeChatService()->handleCloudNotification(
            ['wechat_notification_enabled' => 1],
            ['wechat_notification_enabled' => 0],
            []
        );

        $this->assertFalse($result);

        ReflectionUtils::setStaticProperty(new CloudAPIFactory(), 'api', null);
    }

    public function testHandleCloudNotificationWithCloudClosed()
    {
        $mockApiClient = \Mockery::mock('Biz\CloudPlatform\Client\CloudAPI');
        $mockApiClient->shouldReceive('get')->andReturn([]);
        ReflectionUtils::setStaticProperty(new CloudAPIFactory(), 'api', $mockApiClient);

        $result = $this->getWeChatService()->handleCloudNotification(
            ['wechat_notification_enabled' => 1],
            ['wechat_notification_enabled' => 0],
            []
        );

        $this->assertFalse($result);

        ReflectionUtils::setStaticProperty(new CloudAPIFactory(), 'api', null);
    }

    public function testHandleCloudNotificationWithOpenChannelFail()
    {
        $mockApiClient = \Mockery::mock('Biz\CloudPlatform\Client\CloudAPI');
        $mockApiClient->shouldReceive('get')->andReturn(['accessCloud' => true]);
        ReflectionUtils::setStaticProperty(new CloudAPIFactory(), 'api', $mockApiClient);

        $biz = $this->getBiz();
        $mockNotificationService = \Mockery::mock('QiQiuYun\SDK\Service\NotificationService');
        $mockNotificationService->shouldReceive('openAccount')->andReturn();
        $mockNotificationService->shouldReceive('openChannel')->andReturn([]);
        $biz['ESCloudSdk.notification'] = $mockNotificationService;

        $result = $this->getWeChatService()->handleCloudNotification(
            ['wechat_notification_enabled' => 0],
            ['wechat_notification_enabled' => 1],
            [
                'weixinmob_key' => '',
                'weixinmob_secret' => '',
            ]
        );

        $this->assertFalse($result);

        ReflectionUtils::setStaticProperty(new CloudAPIFactory(), 'api', null);
    }

    public function testHandleCloudNotificationOpenChannel()
    {
        $mockApiClient = \Mockery::mock('Biz\CloudPlatform\Client\CloudAPI');
        $mockApiClient->shouldReceive('get')->andReturn(['accessCloud' => true]);
        ReflectionUtils::setStaticProperty(new CloudAPIFactory(), 'api', $mockApiClient);

        $biz = $this->getBiz();
        $mockNotificationService = \Mockery::mock('QiQiuYun\SDK\Service\NotificationService');
        $mockNotificationService->shouldReceive('openAccount')->andReturn('');
        $mockNotificationService->shouldReceive('openChannel')->andReturn(['type' => 'wechat']);
        $biz['ESCloudSdk.notification'] = $mockNotificationService;

        $result = $this->getWeChatService()->handleCloudNotification(
            ['wechat_notification_enabled' => 0],
            ['wechat_notification_enabled' => 1],
            [
                'weixinmob_key' => '',
                'weixinmob_secret' => '',
            ]
        );

        $this->assertTrue($result);

        ReflectionUtils::setStaticProperty(new CloudAPIFactory(), 'api', null);
    }

    public function testHandleCloudNotificationWithCloseChannelFail()
    {
        $mockApiClient = \Mockery::mock('Biz\CloudPlatform\Client\CloudAPI');
        $mockApiClient->shouldReceive('get')->andReturn(['accessCloud' => true]);
        ReflectionUtils::setStaticProperty(new CloudAPIFactory(), 'api', $mockApiClient);

        $biz = $this->getBiz();
        $mockNotificationService = \Mockery::mock('QiQiuYun\SDK\Service\NotificationService');
        $mockNotificationService->shouldReceive('closeAccount')->andReturn();
        $mockNotificationService->shouldReceive('closeChannel')->andReturn([]);
        $biz['ESCloudSdk.notification'] = $mockNotificationService;

        $result = $this->getWeChatService()->handleCloudNotification(
            ['wechat_notification_enabled' => 1],
            ['wechat_notification_enabled' => 0],
            [
                'weixinmob_key' => '',
                'weixinmob_secret' => '',
            ]
        );

        $this->assertFalse($result);

        ReflectionUtils::setStaticProperty(new CloudAPIFactory(), 'api', null);
    }

    public function testHandleCloudNotificationCloseChannel()
    {
        $mockApiClient = \Mockery::mock('Biz\CloudPlatform\Client\CloudAPI');
        $mockApiClient->shouldReceive('get')->andReturn(['accessCloud' => true]);
        ReflectionUtils::setStaticProperty(new CloudAPIFactory(), 'api', $mockApiClient);

        $biz = $this->getBiz();
        $mockNotificationService = \Mockery::mock('QiQiuYun\SDK\Service\NotificationService');
        $mockNotificationService->shouldReceive('closeAccount')->andReturn('');
        $mockNotificationService->shouldReceive('closeChannel')->andReturn(['type' => 'wechat']);
        $biz['ESCloudSdk.notification'] = $mockNotificationService;

        $result = $this->getWeChatService()->handleCloudNotification(
            ['wechat_notification_enabled' => 1],
            ['wechat_notification_enabled' => 0],
            [
                'weixinmob_key' => '',
                'weixinmob_secret' => '',
            ]
        );

        $this->assertTrue($result);

        ReflectionUtils::setStaticProperty(new CloudAPIFactory(), 'api', null);
    }

    public function testGetJobs()
    {
        $result = $this->getWeChatService()->getJobs();
        $this->assertEquals('WeChatUsersSyncJob', $result[0]['name']);
        $this->assertEquals('WeChatUserFreshJob', $result[1]['name']);
    }

    public function testSearchWeChatUsersJoinUser()
    {
        $this->mockCreateWeChatUser(['openId' => 'www']);

        $result = $this->getWeChatService()->searchWeChatUsersJoinUser(['userId' => 1], ['lastRefreshTime' => 'ASC'], 0, 10);

        $this->assertEquals('1', $result[0]['userId']);
    }

    public function testCountWeChatUsersJoinUser()
    {
        $this->mockCreateWeChatUser(['openId' => 'www']);

        $result = $this->getWeChatService()->countWeChatUserJoinUser(['userId' => 1]);

        $this->assertEquals(1, $result);
    }

    public function testGetWeChatSendChannel()
    {
        $this->getSettingService()->set('wechat', [
            'wechat_notification_enabled' => 1,
            'is_authorization' => 1,
        ]);
        $res = $this->getWeChatService()->getWeChatSendChannel();
        $this->assertEquals('wechat_agent', $res);
    }

    public function testGetWeChatSendChannelWithNoAuth()
    {
        $res = $this->getWeChatService()->getWeChatSendChannel();
        $this->assertEquals('wechat', $res);
    }

    public function testSaveWeChatTemplateSetting()
    {
        $wechatSetting = [
            'wechat_notification_enabled' => 1,
            'templates' => [
                'homeworkOrTestPaperReview' => [
                    'templateId' => 'testId',
                    'status' => 1,
                    'sendTime' => '11:20',
                ],
                'courseRemind' => [
                    'templateId' => 'testId',
                    'status' => 1,
                    'sendTime' => '11:20',
                    'sendDays' => ['Mon'],
                ],
            ],
        ];
        $this->mockBiz('System:SettingService', [
            [
                'functionName' => 'get',
                'withParams' => ['wechat', []],
                'returnValue' => $wechatSetting,
            ],
            [
                'functionName' => 'set',
                'returnValue' => $wechatSetting,
            ],
            [
                'functionName' => 'node',
                'withParams' => ['site.url'],
                'returnValue' => 'http://www.baidu.com',
            ],
        ]);

        $this->getSettingService()->set('wechat', $wechatSetting);

        $this->getWeChatService()->saveWeChatTemplateSetting('homeworkOrTestPaperReview', [
            'templateId' => 'testId',
            'status' => 1,
            'sendTime' => '11:20',
        ], 'serviceFollow');
        $schedulerJobs = $this->getSchedulerService()->searchJobs(['name' => 'WeChatNotificationJob_HomeWorkOrTestPaperReview'], [], 0, 1);
        $this->assertEquals('WeChatNotificationJob_HomeWorkOrTestPaperReview', $schedulerJobs[0]['name']);

        $this->getWeChatService()->saveWeChatTemplateSetting('courseRemind', [
            'templateId' => 'testId',
            'status' => 1,
            'sendTime' => '11:20',
            'sendDays' => ['Mon'],
        ], 'serviceFollow');
        $schedulerJobs = $this->getSchedulerService()->searchJobs(['name' => 'WeChatNotificationJob_CourseRemind'], [], 0, 1);
        $this->assertEquals('WeChatNotificationJob_CourseRemind', $schedulerJobs[0]['name']);
    }

    public function testSynchronizeSubscriptionRecords()
    {
        $biz = $this->getBiz();
        $mockNotificationService = \Mockery::mock('ESCloud\SDK\Service\NotificationService');
        $mockNotificationService->shouldReceive('searchRecords')->andReturn(['data' => [['to_id' => 'test', 'template_code' => 'test', 'created_time' => '']], 'paging' => ['total' => 10]]);
        $biz['ESCloudSdk.notification'] = $mockNotificationService;

        $result = $this->getWeChatService()->synchronizeSubscriptionRecords();

        $this->assertNull($result);

        $result = $this->getSubscribeRecordDao()->count([]);

        $this->assertNotEmpty($result);
    }

    protected function mockCreateWeChatUser($fields = [])
    {
        $user = $this->getCurrentUser();

        $data = [
            'appId' => 'ssssssssssssssssssss',
            'type' => 'official',
            'userId' => $user['id'],
            'openId' => 'ffffffffffffffffffff',
            'unionId' => 'hhhhhhhhhhhhhhhhhhhh',
        ];

        $data = array_merge($data, $fields);

        return $this->getWeChatService()->createWeChatUser($data);
    }

    /**
     * @return WeChatService
     */
    protected function getWeChatService()
    {
        return $this->createService('WeChat:WeChatService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return SchedulerService
     */
    protected function getSchedulerService()
    {
        return $this->createService('Scheduler:SchedulerService');
    }

    /**
     * @return SubscribeRecordDao
     */
    protected function getSubscribeRecordDao()
    {
        return $this->createDao('WeChat:SubscribeRecordDao');
    }
}
