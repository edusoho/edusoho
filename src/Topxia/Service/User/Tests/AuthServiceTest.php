<?php

namespace Topxia\Service\User\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\User\AuthService;
use Topxia\Service\User\UserService;
use Topxia\Common\ArrayToolkit;

// TODO

class AuthServiceTest extends BaseTestCase
{


    public function testRegisterWithTypeDefault()
    {
        $user = $this->getAuthService()->register(array(
            'email' => 'test@edusoho.com',
            'nickname' => 'test',
            'password' => '123456',
        ));
        $this->assertEquals($user['email'],'test@edusoho.com');
    }

    public function testRegisterWithOtherType()
    {
    	$makeToken = $this->getUserService()->makeToken('discuz');
    	$getToken = $this->getUserService()->getToken('discuz',$makeToken);
    	$user = $this->getAuthService()->register(array(
            'email' => 'test@edusoho.com',
            'nickname' => 'test',
            'password' => '123456',
            'token' => $getToken,
        ),'discuz');
        $this->assertEquals($user['email'],'test@edusoho.com');
    }

    // public function testSyncLogin()
    // {
    // 	$this->getSettingService()->set('user_partner',array('mode' => 'discuz'));
    // 	$makeToken = $this->getUserService()->makeToken('discuz');
    // 	$getToken = $this->getUserService()->getToken('discuz',$makeToken);
    // 	$user = $this->getAuthService()->register(array(
    //         'email' => 'test@edusoho.com',
    //         'nickname' => 'test',
    //         'password' => '123456',
    //         'token' => $getToken,
    //     ),'discuz');

    //     $this->getAuthService()->syncLogin($user['id']);
    //     $this->getSettingService()->delete('user_partner');
    // }

    public function testChangeNickname()
    {
    	$user = $this->getAuthService()->register(array(
            'email' => 'test@edusoho.com',
            'nickname' => 'test',
            'password' => '123456',
        ));

        $this->getAuthService()->changeNickname($user['id'],'newName');
        $newUser = $this->getUserService()->getUser($user['id']);
        $this->assertEquals('newName',$newUser['nickname']);
    }

    public function testChangeEmail()
    {
    	$user = $this->getAuthService()->register(array(
            'email' => 'test@edusoho.com',
            'nickname' => 'test',
            'password' => '123456',
        ));

        $this->getAuthService()->changeEmail($user['id'],'123456','newemail@edusoho.com');
        $newUser = $this->getUserService()->getUser($user['id']);
        $this->assertEquals('newemail@edusoho.com',$newUser['email']);
    }

    public function testChangePassword()
    {
    	$user = $this->getAuthService()->register(array(
            'email' => 'test@edusoho.com',
            'nickname' => 'test',
            'password' => '123456',
        ));

        $this->getAuthService()->changePassword($user['id'],'123456','654321');
        $newUser = $this->getUserService()->getUser($user['id']);
        $this->assertNotEquals($user['password'],$newUser['password']);
    }

    public function testChangePayPassword()
    {
    	$this->getSettingService()->set('user_partner',array('mode' => 'discuz'));
    	$makeToken = $this->getUserService()->makeToken('discuz');
    	$getToken = $this->getUserService()->getToken('discuz',$makeToken);
    	$user = $this->getAuthService()->register(array(
            'email' => 'test@edusoho.com',
            'nickname' => 'test',
            'password' => '123456',
            'token' => $getToken,
        ),'discuz');

        $this->getAuthService()->changePayPassword($user['id'],'123456','930919');
        $newUser = $this->getUserService()->getUser($user['id']);
        $this->assertNotEquals($user['payPassword'],$newUser['payPassword']);
        $this->getSettingService()->delete('user_partner');
    }

    public function testChangePayPasswordWithoutLoginPassword()
    {
    	$this->getSettingService()->set('user_partner',array('mode' => 'discuz'));
    	$setting = $this->getSettingService()->get('user_partner');
    	$makeToken = $this->getUserService()->makeToken('discuz');
    	$getToken = $this->getUserService()->getToken('discuz',$makeToken);
    	$user = $this->getAuthService()->register(array(
            'email' => 'test@edusoho.com',
            'nickname' => 'test',
            'password' => '123456',
            'token' => $getToken,
        ),'discuz');

        $this->getAuthService()->changePayPasswordWithoutLoginPassword($user['id'],'930919');
        $newUser = $this->getUserService()->getUser($user['id']);
        $this->assertNotEquals($user['payPassword'],$newUser['payPassword']);
        $this->getSettingService()->delete('user_partner');
    }

    public function testRefillFormDataWithoutNicknameAndEmail()
    {
    	$value = array('register_mode' => 'email_or_mobile');
    	$this->getSettingService()->set('auth',$value);
    	$user = $this->getAuthService()->register(array(
            'password' => '123456',
            'emailOrMobile' => '18989492142',
        ));
        $this->assertNotNull($user);
        $this->getSettingService()->delete('auth');
    }

    public function testCheckUserName()
    {

    }



    private function createUser()
    {
        $user = array();
        $user['email'] = "user@user.com";
        $user['nickname'] = "user";
        $user['password'] = "user";
        $user =  $this->getUserService()->register($user);
        $user['currentIp'] = '127.0.0.1';
        $user['roles'] = array('ROLE_USER','ROLE_SUPER_ADMIN','ROLE_TEACHER');
        return $user;

    }

    private function createNormalUser()
    {
        $user = array();
        $user['email'] = "normal@user.com";
        $user['nickname'] = "normal";
        $user['password'] = "user";
        $user =  $this->getUserService()->register($user);
        $user['currentIp'] = '127.0.0.1';
        $user['roles'] = array('ROLE_USER');
        return $user;
    }


    protected function getAuthService()
    {
        return $this->getServiceKernel()->createService('User.AuthService');
    }

    protected function getUserService()
    {
    	return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getSettingService()
    {
    	return $this->getServiceKernel()->createService('System.SettingService');
    }
}