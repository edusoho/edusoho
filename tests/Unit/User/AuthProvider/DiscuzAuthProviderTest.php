<?php

namespace Tests\Unit\User;

use Biz\BaseTestCase;
use AppBundle\Common\ReflectionUtils;
use Biz\User\AuthProvider\DiscuzAuthProvider;

class DiscuzAuthProviderTest extends BaseTestCase
{
    public function testRegister()
    {
        $settingService = $this->mockSettingService();
        $provider = $this->mockDiscuzClient();

        $registration = array(
            'nickname' => 'discuz-nickname',
            'password' => 'discuz-password',
            'email' => 'discuz-email',
        );
        $result = $provider->register($registration);

        $expectedRegistrations = array_merge($registration, array('id' => 0));
        $this->assertArrayEquals($expectedRegistrations, $result);
        $settingService->shouldHaveReceived('get')->times(1);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testRegisterWithExceptionLenghInvalid()
    {
        $settingService = $this->mockSettingService();
        $provider = $this->mockDiscuzClient();

        $registration = array(
            'nickname' => '-1',
            'password' => 'discuz-password',
            'email' => 'discuz-email',
        );
        $result = $provider->register($registration);
        $settingService->shouldHaveReceived('get')->times(1);
    }

    public function testSyncLogin()
    {
        $settingService = $this->mockSettingService();
        $provider = $this->mockDiscuzClient();

        $this->assertTrue($provider->syncLogin(333));
    }

    public function testSyncLogout()
    {
        $settingService = $this->mockSettingService();
        $provider = $this->mockDiscuzClient();

        $this->assertTrue($provider->syncLogout(333));
        $settingService->shouldHaveReceived('get')->times(1);
    }

    public function testChangeNickname()
    {
        $settingService = $this->mockSettingService();
        $provider = $this->mockDiscuzClient();

        $this->assertTrue($provider->changeNickname(333, 'newNickname'));
        $settingService->shouldHaveReceived('get')->times(1);
    }

    public function testChangeEmail()
    {
        $settingService = $this->mockSettingService();
        $provider = $this->mockDiscuzClient();

        $this->assertTrue($provider->changeNickname(333, 'newNickname'));
        $settingService->shouldHaveReceived('get')->times(1);
    }

    public function testChangePassword()
    {
        $settingService = $this->mockSettingService();
        $provider = $this->mockDiscuzClient();

        $this->assertTrue($provider->changePassword(333, '123', 'newPassword'));
        $settingService->shouldHaveReceived('get')->times(1);
    }

    public function testCheckUsername()
    {
        $settingService = $this->mockSettingService();
        $provider = $this->mockDiscuzClient();

        $this->assertArrayEquals(
            array('success', ''),
            $provider->checkUsername('newUsername')
        );
        $settingService->shouldHaveReceived('get')->times(1);
    }

    public function testCheckEmail()
    {
        $settingService = $this->mockSettingService();
        $provider = $this->mockDiscuzClient();

        $this->assertArrayEquals(
            array('success', ''),
            $provider->checkEmail('newEmail')
        );
        $settingService->shouldHaveReceived('get')->times(1);
    }

    public function testCheckMobile()
    {
        $settingService = $this->mockSettingService();
        $provider = $this->mockDiscuzClient();

        $this->assertArrayEquals(
            array('success', ''),
            $provider->checkMobile('')
        );
        $settingService->shouldNotHaveReceived('get');
    }

    public function testCheckConnect()
    {
        $settingService = $this->mockSettingService();
        $provider = $this->mockDiscuzClient();
        $this->assertTrue($provider->checkConnect());
        $settingService->shouldHaveReceived('get')->times(1);
    }

    public function testCheckPassword()
    {
        $settingService = $this->mockSettingService();
        $provider = $this->mockDiscuzClient();
        $this->assertTrue($provider->checkPassword(123, 'password'));
        $settingService->shouldHaveReceived('get')->times(1);
    }

    public function testCheckLoginById()
    {
        $settingService = $this->mockSettingService();
        $provider = $this->mockDiscuzClient();
        $this->assertArrayEquals(
            array(
                'id' => 1,
                'nickname' => 'nickname',
                'email' => 'email@howzhi.com',
                'createdTime' => '',
                'createdIp' => '',
            ),
            $provider->checkLoginById(123, 'password')
        );
        $settingService->shouldHaveReceived('get')->times(1);
    }

    public function testCheckLoginByNickname()
    {
        $settingService = $this->mockSettingService();
        $provider = $this->mockDiscuzClient();
        $this->assertArrayEquals(
            array(
                'id' => 1,
                'nickname' => 'nickname',
                'email' => 'email@howzhi.com',
                'createdTime' => '',
                'createdIp' => '',
            ),
            $provider->checkLoginByNickname('nickname', 'password')
        );
        $settingService->shouldHaveReceived('get')->times(1);
    }

    public function testCheckLoginByEmail()
    {
        $settingService = $this->mockSettingService();
        $provider = $this->mockDiscuzClient();
        $this->assertArrayEquals(
            array(
                'id' => 1,
                'nickname' => 'nickname',
                'email' => 'email@howzhi.com',
                'createdTime' => '',
                'createdIp' => '',
            ),
            $provider->checkLoginByEmail('email', 'password')
        );
        $settingService->shouldHaveReceived('get')->times(1);
    }

    public function testGetAvatar()
    {
        $settingService = $this->mockSettingService();
        $provider = $this->mockDiscuzClient();
        $this->assertEquals(
            'UC_API_VALUE/avatar.php?uid=123&type=virtual&size=middle',
            $provider->getAvatar('123', 'middle')
        );
        $settingService->shouldHaveReceived('get')->times(1);
    }

    public function testGetProviderName()
    {
        $settingService = $this->mockSettingService();
        $provider = $this->mockDiscuzClient();

        $this->assertEquals('discuz', $provider->getProviderName());
    }

    private function mockDiscuzClient()
    {
        $biz = $this->getBiz();
        $mockedDiscuzClientPath = $biz['topxia.upload.private_directory'].
            '/../../../tests/Unit/User/AuthProvider/Tools/MockedDiscuzClient.php';
        $provider = new DiscuzAuthProvider($biz);
        $provider = ReflectionUtils::setProperty(
            $provider,
            'mockedDiscusClientPath',
            $mockedDiscuzClientPath
        );

        return $provider;
    }

    private function mockSettingService()
    {
        if (!defined('UC_CHARSET')) {
            define('UC_CHARSET', 'gbk');
            define('UC_API', 'UC_API_VALUE');
        }

        $randConfig = $this->createRandConfig();

        return $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'withParams' => array('user_partner'),
                    'returnValue' => array(
                        'partner_config' => array(
                            'discuz' => array(
                                $randConfig => $randConfig,
                            ),
                        ),
                    ),
                ),
            )
        );
    }

    private function createRandConfig()
    {
        $randNum = rand(0, PHP_INT_MAX);

        $randConfig = 'config_'.$randNum;

        if (defined($randConfig)) {
            return $this->createRandConfig();
        }

        return $randConfig;
    }
}
