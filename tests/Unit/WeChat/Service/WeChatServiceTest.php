<?php

namespace Tests\Unit\WeChat\Service;

use Biz\BaseTestCase;
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

    public function testRreshOfficialWeChatUserWhenLogin()
    {
        $weChatUser = $this->mockCreateWeChatUser(array('openId' => 'www', 'userId' => 2));
        $this->getWeChatService()->freshOfficialWeChatUserWhenLogin(array('id' => 10), array(), array('openid' => 'www'));

        $result = $this->getWeChatService()->getWeChatUser($weChatUser['id']);
        $this->assertEquals(10, $result['userId']);

        $this->getWeChatService()->freshOfficialWeChatUserWhenLogin(array('id' => 9), array('fromId' => 'ppp'), array('openid' => 'uuu'));
        $result = $this->getWeChatService()->getWeChatUserByTypeAndOpenId('official', 'uuu');
        $this->assertEquals(9, $result['userId']);
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
