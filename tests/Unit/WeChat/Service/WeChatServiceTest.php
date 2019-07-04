<?php

namespace Tests\Unit\WeChat\Service;

use AppBundle\Common\ReflectionUtils;
use Biz\BaseTestCase;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\System\Service\SettingService;
use Biz\WeChat\Service\WeChatService;

class WeChatServiceTest extends BaseTestCase
{
    /**
     * @expectedException \Biz\Common\CommonException
     * @expectedExceptionMessage exception.common_parameter_missing
     */
    public function testCreateWeChatUserWithErrorParams()
    {
        $this->getWeChatService()->createWeChatUser(array());
    }

    public function testGetWeChatUserByTypeAndUnionId()
    {
        $this->mockCreateWeChatUser(array('unionId' => 'qqq'));
        $result = $this->getWeChatService()->getWeChatUserByTypeAndUnionId('official', 'qqq');
        $this->assertEquals('qqq', $result['unionId']);

        $result = $this->getWeChatService()->getWeChatUserByTypeAndUnionId('open-app', 'qqq');
        $this->assertEmpty($result);
    }

    public function testGetWeChatUserByTypeAndOpenId()
    {
        $this->mockCreateWeChatUser(array('openId' => 'www'));
        $result = $this->getWeChatService()->getWeChatUserByTypeAndOpenId('official', 'www');
        $this->assertEquals('www', $result['openId']);

        $result = $this->getWeChatService()->getWeChatUserByTypeAndOpenId('open-app', 'www');
        $this->assertEmpty($result);
    }

    public function testFindWeChatUsersByUserId()
    {
        $this->mockCreateWeChatUser(array('userId' => 2));
        $result = $this->getWeChatService()->findWeChatUsersByUserId(2);
        $this->assertEquals(2, $result[0]['userId']);
    }

    public function testFindSubscribedUsersByUserIdsAndType()
    {
        $result = $this->getWeChatService()->findSubscribedUsersByUserIdsAndType(array(1), 'open-app');
        $this->assertEmpty($result);
    }

    public function testFindWeChatUsersByUserIdAndType()
    {
        $this->mockCreateWeChatUser(array('userId' => 2));
        $result = $this->getWeChatService()->findWeChatUsersByUserIdAndType(2, 'official');
        $this->assertEquals(2, $result[0]['userId']);
    }

    public function testGetOfficialWeChatUserByUserId()
    {
        $result = $this->getWeChatService()->getOfficialWeChatUserByUserId(2);
        $this->assertEmpty($result);

        $this->mockCreateWeChatUser(array('userId' => 2, 'lastRefreshTime' => time() + 86400));
        $result = $this->getWeChatService()->getOfficialWeChatUserByUserId(2);
        $this->assertEquals(2, $result['userId']);
    }

    public function testUpdateWeChatUser()
    {
        $weChatUser = $this->mockCreateWeChatUser(array('userId' => 2));
        $this->getWeChatService()->updateWeChatUser($weChatUser['id'], array('userId' => 3));

        $result = $this->getWeChatService()->getWeChatUser($weChatUser['id']);
        $this->assertEquals(3, $result['userId']);
    }

    /**
     * @expectedException \Biz\User\UserException
     * @expectedExceptionMessage exception.user.not_found
     */
    public function testUpdateWeChatUserWithExistUser()
    {
        $this->getWeChatService()->updateWeChatUser(1, array());
    }

    public function testSearchWeChatUsers()
    {
        $this->mockCreateWeChatUser(array('openId' => 'www'));

        $result = $this->getWeChatService()->searchWeChatUsers(array('userId' => 1), array('lastRefreshTime' => 'ASC'), 0, 10, array('id', 'openId', 'unionId', 'userId'));

        $this->assertEquals('www', $result[0]['openId']);
    }

    public function testBatchSyncOfficialWeChatUsers()
    {
        $biz = $this->getBiz();
        $mockClient = \Mockery::mock('AppBundle\Component\Notification\WeChatTemplateMessage\Client');
        $mockClient->shouldReceive('getUserList')->andReturn(array(
            'data' => array('openid' => array()),
            'next_openid' => 'thisisafakenextopenid',
        ));

        $biz['wechat.template_message_client'] = $mockClient;

        $result = $this->getWeChatService()->batchSyncOfficialWeChatUsers();
        $this->assertEquals(array('next_openid' => 'thisisafakenextopenid'), $result);
    }

    public function testBatchSyncOfficialWeChatUsersWithoutNewOpenIds()
    {
        $biz = $this->getBiz();
        $mockClient = \Mockery::mock('AppBundle\Component\Notification\WeChatTemplateMessage\Client');

        $mockClient->shouldReceive('getUserList')->andReturn(array(
            'data' => array('openid' => array(1, 2, 3)),
            'next_openid' => 'thisisafakenextopenid',
        ));

        $biz['wechat.template_message_client'] = $mockClient;

        $this->mockBiz(
            'WeChat:UserWeChatDao',
            array(
                array(
                    'functionName' => 'findOpenIdsInListsByType',
                    'returnValue' => array(
                        array('openId' => 1),
                        array('openId' => 2),
                        array('openId' => 3),
                    ),
                ),
            )
        );

        $result = $this->getWeChatService()->batchSyncOfficialWeChatUsers();
        $this->assertEmpty($result);
    }

    public function testBatchSyncOfficialWeChatUsersWithNewOpenIds()
    {
        $biz = $this->getBiz();
        $mockClient = \Mockery::mock('AppBundle\Component\Notification\WeChatTemplateMessage\Client');

        $mockClient->shouldReceive('getUserList')->andReturn(array(
            'data' => array('openid' => array(1, 2, 3)),
            'next_openid' => 'thisisafakenextopenid',
        ));

        $mockClient->shouldReceive('getAppId')->andReturn('thisisappid');

        $biz['wechat.template_message_client'] = $mockClient;

        $this->mockBiz(
            'WeChat:UserWeChatDao',
            array(
                array(
                    'functionName' => 'findOpenIdsInListsByType',
                    'returnValue' => array(
                        array('openId' => 1),
                        array('openId' => 2),
                    ),
                ),
                array(
                    'functionName' => 'batchCreate',
                ),
            )
        );

        $result = $this->getWeChatService()->batchSyncOfficialWeChatUsers();
        $this->assertEquals(array('next_openid' => 'thisisafakenextopenid'), $result);
    }

    public function testFreshOfficialWeChatUserWhenLogin()
    {
        $weChatUser = $this->mockCreateWeChatUser(array('openId' => 'www', 'userId' => 2));
        $this->getWeChatService()->freshOfficialWeChatUserWhenLogin(array('id' => 10), array(), array('openid' => 'www'));

        $result = $this->getWeChatService()->getWeChatUser($weChatUser['id']);
        $this->assertEquals(10, $result['userId']);

        $this->getWeChatService()->freshOfficialWeChatUserWhenLogin(array('id' => 9), array('fromId' => 'ppp'), array('openid' => 'uuu'));
        $result = $this->getWeChatService()->getWeChatUserByTypeAndOpenId('official', 'uuu');
        $this->assertEquals(9, $result['userId']);
    }

    public function testBatchFreshOfficialWeChatUsers()
    {
        $weChatUser1 = $this->mockCreateWeChatUser(array('userId' => 3));
        $weChatUser2 = $this->mockCreateWeChatUser(array('userId' => 4, 'unionId' => 'thisisunionid', 'openId' => 'thisisopenid'));

        $freshWeChatUser1 = array(
            'openid' => 'ffffffffffffffffffff',
            'unionid' => 'hhhhhhhhhhhhhhhhhhhh',
            'subscribe' => 0,
        );

        $freshWeChatUser2 = array(
            'openid' => 'thisisopenid',
            'unionid' => 'thisisunionid',
            'subscribe' => 1,
        );

        $mockClient = \Mockery::mock('AppBundle\Component\Notification\WeChatTemplateMessage\Client');
        $mockClient->shouldReceive('batchGetUserInfo')->andReturn(array(
            $freshWeChatUser1,
            $freshWeChatUser2,
        ));

        $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'findUserBindByTypeAndToIds',
                    'returnValue' => array(
                        array(
                            'fromId' => 'hhhhhhhhhhhhhhhhhhhh',
                            'toId' => 3,
                        ),
                        array(
                            'fromId' => 'thisisunionid',
                            'toId' => 4,
                        ),
                    ),
                ),
                array(
                    'functionName' => 'findUserBindByTypeAndFromIds',
                    'returnValue' => array(
                        array(
                            'fromId' => 'hhhhhhhhhhhhhhhhhhhh',
                            'toId' => 3,
                        ),
                        array(
                            'fromId' => 'thisisunionid',
                            'toId' => 4,
                        ),
                    ),
                ),
            )
        );

        $biz = $this->getBiz();
        $biz['wechat.template_message_client'] = $mockClient;
        $this->getWeChatService()->batchFreshOfficialWeChatUsers(array($weChatUser1, $weChatUser2));

        $result1 = $this->getWeChatService()->getOfficialWeChatUserByUserId(3);
        $result2 = $this->getWeChatService()->getOfficialWeChatUserByUserId(4);

        $this->assertEquals($freshWeChatUser1, $result1['data']);
        $this->assertEquals($freshWeChatUser2, $result2['data']);
    }

    public function testGetTemplateIdWithUnabledSetting()
    {
        $wechatSetting = array(
            'wechat_notification_enabled' => 0,
        );
        $this->getSettingService()->set('wechat', $wechatSetting);
        $result = $this->getWeChatService()->getTemplateId(1);
        $this->assertEmpty($result);
    }

    public function testGetTemplateIdWithEmptyTemplateId()
    {
        $wechatSetting = array(
            'wechat_notification_enabled' => 1,
            1 => array(
                'status' => 'open',
                'templateId' => null,
            ),
        );
        $this->getSettingService()->set('wechat', $wechatSetting);
        $result = $this->getWeChatService()->getTemplateId(1);
        $this->assertEmpty($result);
    }

    public function testGetTemplateId()
    {
        $wechatSetting = array(
            'wechat_notification_enabled' => 1,
            'abc' => array(
                'status' => 'open',
                'templateId' => 123,
            ),
        );
        $this->getSettingService()->set('wechat', $wechatSetting);
        $result = $this->getWeChatService()->getTemplateId('abc');
        $this->assertEquals(123, $result);
    }

    public function testHandleCloudNotificationWithSameSetting()
    {
        $setting = array(
            'wechat_notification_enabled' => 1,
        );

        $result = $this->getWeChatService()->handleCloudNotification($setting, $setting, array());
        $this->assertTrue($result);

        $mockApiClient = \Mockery::mock('Biz\CloudPlatform\Client\CloudAPI');
        $mockApiClient->shouldReceive('get')->andThrow('\RuntimeException');
        ReflectionUtils::setStaticProperty(new CloudAPIFactory(), 'api', $mockApiClient);

        $result = $this->getWeChatService()->handleCloudNotification(
            array('wechat_notification_enabled' => 1),
            array('wechat_notification_enabled' => 0),
            array()
        );

        $this->assertFalse($result);

        ReflectionUtils::setStaticProperty(new CloudAPIFactory(), 'api', null);
    }

    public function testHandleCloudNotificationWithCloudClosed()
    {
        $mockApiClient = \Mockery::mock('Biz\CloudPlatform\Client\CloudAPI');
        $mockApiClient->shouldReceive('get')->andReturn(array());
        ReflectionUtils::setStaticProperty(new CloudAPIFactory(), 'api', $mockApiClient);

        $result = $this->getWeChatService()->handleCloudNotification(
            array('wechat_notification_enabled' => 1),
            array('wechat_notification_enabled' => 0),
            array()
        );

        $this->assertFalse($result);

        ReflectionUtils::setStaticProperty(new CloudAPIFactory(), 'api', null);
    }

    public function testHandleCloudNotificationWithOpenChannelFail()
    {
        $mockApiClient = \Mockery::mock('Biz\CloudPlatform\Client\CloudAPI');
        $mockApiClient->shouldReceive('get')->andReturn(array('accessCloud' => true));
        ReflectionUtils::setStaticProperty(new CloudAPIFactory(), 'api', $mockApiClient);

        $biz = $this->getBiz();
        $mockNotificationService = \Mockery::mock('QiQiuYun\SDK\Service\NotificationService');
        $mockNotificationService->shouldReceive('openAccount')->andReturn();
        $mockNotificationService->shouldReceive('openChannel')->andReturn(array());
        $biz['qiQiuYunSdk.notification'] = $mockNotificationService;

        $result = $this->getWeChatService()->handleCloudNotification(
            array('wechat_notification_enabled' => 0),
            array('wechat_notification_enabled' => 1),
            array(
                'weixinmob_key' => '',
                'weixinmob_secret' => '',
            )
        );

        $this->assertFalse($result);

        ReflectionUtils::setStaticProperty(new CloudAPIFactory(), 'api', null);
    }

    public function testHandleCloudNotificationOpenChannel()
    {
        $mockApiClient = \Mockery::mock('Biz\CloudPlatform\Client\CloudAPI');
        $mockApiClient->shouldReceive('get')->andReturn(array('accessCloud' => true));
        ReflectionUtils::setStaticProperty(new CloudAPIFactory(), 'api', $mockApiClient);

        $biz = $this->getBiz();
        $mockNotificationService = \Mockery::mock('QiQiuYun\SDK\Service\NotificationService');
        $mockNotificationService->shouldReceive('openAccount')->andReturn('');
        $mockNotificationService->shouldReceive('openChannel')->andReturn(array('type' => 'wechat'));
        $biz['qiQiuYunSdk.notification'] = $mockNotificationService;

        $result = $this->getWeChatService()->handleCloudNotification(
            array('wechat_notification_enabled' => 0),
            array('wechat_notification_enabled' => 1),
            array(
                'weixinmob_key' => '',
                'weixinmob_secret' => '',
            )
        );

        $this->assertTrue($result);

        ReflectionUtils::setStaticProperty(new CloudAPIFactory(), 'api', null);
    }

    public function testHandleCloudNotificationWithCloseChannelFail()
    {
        $mockApiClient = \Mockery::mock('Biz\CloudPlatform\Client\CloudAPI');
        $mockApiClient->shouldReceive('get')->andReturn(array('accessCloud' => true));
        ReflectionUtils::setStaticProperty(new CloudAPIFactory(), 'api', $mockApiClient);

        $biz = $this->getBiz();
        $mockNotificationService = \Mockery::mock('QiQiuYun\SDK\Service\NotificationService');
        $mockNotificationService->shouldReceive('closeAccount')->andReturn();
        $mockNotificationService->shouldReceive('closeChannel')->andReturn(array());
        $biz['qiQiuYunSdk.notification'] = $mockNotificationService;

        $result = $this->getWeChatService()->handleCloudNotification(
            array('wechat_notification_enabled' => 1),
            array('wechat_notification_enabled' => 0),
            array(
                'weixinmob_key' => '',
                'weixinmob_secret' => '',
            )
        );

        $this->assertFalse($result);

        ReflectionUtils::setStaticProperty(new CloudAPIFactory(), 'api', null);
    }

    public function testHandleCloudNotificationCloseChannel()
    {
        $mockApiClient = \Mockery::mock('Biz\CloudPlatform\Client\CloudAPI');
        $mockApiClient->shouldReceive('get')->andReturn(array('accessCloud' => true));
        ReflectionUtils::setStaticProperty(new CloudAPIFactory(), 'api', $mockApiClient);

        $biz = $this->getBiz();
        $mockNotificationService = \Mockery::mock('QiQiuYun\SDK\Service\NotificationService');
        $mockNotificationService->shouldReceive('closeAccount')->andReturn('');
        $mockNotificationService->shouldReceive('closeChannel')->andReturn(array('type' => 'wechat'));
        $biz['qiQiuYunSdk.notification'] = $mockNotificationService;

        $result = $this->getWeChatService()->handleCloudNotification(
            array('wechat_notification_enabled' => 1),
            array('wechat_notification_enabled' => 0),
            array(
                'weixinmob_key' => '',
                'weixinmob_secret' => '',
            )
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

    protected function mockCreateWeChatUser($fields = array())
    {
        $user = $this->getCurrentUser();

        $data = array(
            'appId' => 'ssssssssssssssssssss',
            'type' => 'official',
            'userId' => $user['id'],
            'openId' => 'ffffffffffffffffffff',
            'unionId' => 'hhhhhhhhhhhhhhhhhhhh',
        );

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
}
