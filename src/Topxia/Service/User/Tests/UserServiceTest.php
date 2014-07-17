<?php

namespace Topxia\Service\User\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Common\ArrayToolkit;

class UserServiceTest extends BaseTestCase
{   
    /**
     * @group current
     * @return [type] [description]
     */
    public function testRegister()
    {
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);

        $this->assertEquals($userInfo['nickname'], $registeredUser['nickname']);
        $this->assertEquals($userInfo['email'], $registeredUser['email']);
        $this->assertTrue($this->getUserService()->verifyPassword($registeredUser['id'], $userInfo['password']));

        /*default value Test*/
        $this->assertEquals('default', $registeredUser['type']);
        $this->assertEquals(0, $registeredUser['point']);
        $this->assertEquals(0, $registeredUser['coin']);
        $this->assertEquals(0, $registeredUser['locked']);
        $this->assertEquals(0, $registeredUser['loginTime']);
        $this->assertEquals(0, $registeredUser['emailVerified']);
        $this->assertEquals(array('ROLE_USER'), $registeredUser['roles']);
    }

    public function testRegisterByNotDefault()
    {
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com',
            'token'=> array('userId'=>999, 'token'=>'token', 'expiredTime'=>strtotime('+1 day'))
        );
        $registeredUser = $this->getUserService()->register($userInfo, 'qq');

        $this->assertEquals($userInfo['nickname'], $registeredUser['nickname']);
        $this->assertEquals($userInfo['email'], $registeredUser['email']);
        $this->assertFalse($this->getUserService()->verifyPassword($registeredUser['id'], $userInfo['password']));

        /*default value Test*/
        $this->assertEquals('qq', $registeredUser['type']);
        $this->assertEquals(0, $registeredUser['point']);
        $this->assertEquals(0, $registeredUser['coin']);
        $this->assertEquals(0, $registeredUser['locked']);
        $this->assertEquals(0, $registeredUser['loginTime']);
        $this->assertEquals(0, $registeredUser['emailVerified']);
        $this->assertEquals(array('ROLE_USER'), $registeredUser['roles']);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testRegisterWithErrorEmail()
    {
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@error_email.com'
        );
        $this->getUserService()->register($userInfo);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testRegisterWithRegistedNickname()
    {
        $user1Info = array(
            'nickname'=>'testuser1', 
            'password'=> 'test_password',
            'email'=>'test_email@email1.com'
        );
        $this->getUserService()->register($user1Info);

        $user2Info = array(
            'nickname'=>'testuser1',
            'password'=> 'test_password',
            'email'=>'test_email@email2.com'
        );
        $this->getUserService()->register($user2Info);
    }


    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testRegisterWithRegistedEmail()
    {
        $user1Info = array(
            'nickname'=>'testuser1', 
            'password'=> 'test_password',
            'email'=>'test_email@registerdemail.com'
        );
        $this->getUserService()->register($user1Info);

        $user2Info = array(
            'nickname'=>'testuser2',
            'password'=> 'test_password',
            'email'=>'test_email@registerdemail.com'
        );
        $this->getUserService()->register($user2Info);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testRegisterWithErrorNickname1()
    {
        $this->getUserService()->register(array(
            'nickname'=>'test_user nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        ));
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testRegisterWithErrorNickname2()
    {
        $this->getUserService()->register(array(
            'nickname'=>'user|!@2', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        ));
    }

    public function testGetUser()
    {
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $foundUser = $this->getUserService()->getUser($registeredUser['id']);
        $this->assertEquals($registeredUser, $foundUser);

        $foundUser = $this->getUserService()->getUser(999);
        $this->assertNull($foundUser);
    }


    public function testGetUserByNickname()
    {
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $foundUser = $this->getUserService()->getUserByNickname($registeredUser['nickname']);
        $this->assertEquals($registeredUser, $foundUser);

        $foundUser = $this->getUserService()->getUserByNickname('not_exist_nickname');
        $this->assertNull($foundUser);
    }

    public function testGetUserByEmail()
    {
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $foundUser = $this->getUserService()->getUserByEmail('test_email@email.com');
        $this->assertEquals($registeredUser, $foundUser);

        $foundUser = $this->getUserService()->getUserByEmail('not_exist_email@user.com');
        $this->assertNull($foundUser);
    }

    public function testFindUsersByIds()
    {
        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');
        $user3 = $this->createUser('user3');
        $foundUsers = $this->getUserService()->findUsersByIds(array($user1['id'], $user2['id']));

        $foundUserIds = array_keys($foundUsers);
        $this->assertEquals(2, count($foundUserIds));
        $this->assertContains($user1['id'], $foundUserIds);
        $this->assertContains($user2['id'], $foundUserIds);

        $foundUsers = $this->getUserService()->findUsersByIds(array($user1['id'], $user2['id'], 99999));
        $foundUserIds = array_keys($foundUsers);

        $this->assertEquals(2, count($foundUserIds));
        $this->assertContains($user1['id'], $foundUserIds);
        $this->assertContains($user2['id'], $foundUserIds);
        $this->assertNotContains(99999, $foundUserIds);

        $foundUsers = $this->getUserService()->findUsersByIds(array(99999));
        $this->assertEmpty($foundUsers);
    }

    public function testFindUserProfilesByIds()
    {
        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');

        $foundUserProfiles = $this->getUserService()->findUserProfilesByIds(array($user1['id'], $user2['id']));
        $userProfileIds = array_keys($foundUserProfiles);
        $this->assertEquals(2, count($foundUserProfiles));
        $this->assertContains($user1['id'], $userProfileIds);
        $this->assertContains($user2['id'], $userProfileIds);

        $foundUserProfiles = $this->getUserService()->findUserProfilesByIds(array($user1['id'], $user2['id'], 999));
        $userProfileIds = array_keys($foundUserProfiles);
        $this->assertEquals(2, count($foundUserProfiles));
        $this->assertContains($user1['id'], $userProfileIds);
        $this->assertContains($user2['id'], $userProfileIds);

        $foundUserProfiles = $this->getUserService()->findUserProfilesByIds(array(999));
        $this->assertEmpty($foundUserProfiles);
    }

    /**
     *  @group current
     */
    public function testSearchUsersWithOneParamter()
    {
        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');

        $foundUsers = $this->getUserService()->searchUsers(array('nickname'=>'user1'), array('createdTime', 'DESC'), 0, 10);

        $foundUsers = $this->getUserService()->searchUsers(array('roles'=>'ROLE_USER'), array('createdTime', 'DESC'), 0, 10);

        $foundUsers = $this->getUserService()->searchUsers(array('loginIp'=>''), array('createdTime', 'DESC'), 0, 10);

        $foundUsers = $this->getUserService()->searchUsers(array('nickname'=>'user'), array('createdTime', 'DESC'), 0, 10);

        $foundUsers = $this->getUserService()->searchUsers(array('email'=>'user1@user1.com'), array('createdTime', 'DESC'), 0, 10);

        $foundUsers = $this->getUserService()->searchUsers(array('email'=>'user2@user2.com'), array('createdTime', 'DESC'), 0, 10);
    }

    public function testSearchUsersWithOneParamterAndResultEqualsEmpty()
    {
        $foundUsers = $this->getUserService()->searchUsers(array('nickname'=>'user1'), array('createdTime', 'DESC'), 0, 10);
        $this->assertEmpty($foundUsers);

        $foundUsers = $this->getUserService()->searchUsers(array('roles'=>'ROLE_USER'), array('createdTime', 'DESC'), 0, 10);
       
        $foundUsers = $this->getUserService()->searchUsers(array('loginIp'=>''), array('createdTime', 'DESC'), 0, 10);

        $foundUsers = $this->getUserService()->searchUsers(array('nickname'=>'user'), array('createdTime', 'DESC'), 0, 10);

        $foundUsers = $this->getUserService()->searchUsers(array('email'=>'user1@user1.com'), array('createdTime', 'DESC'), 0, 10);
    }

    public function testSearchUsersWithMultiParamter()
    {
        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');

        $foundUsers = $this->getUserService()->searchUsers(array(
            'nickname'=>'user1', 
            'roles'=>'ROLE_USER',
            'loginIp'=>'',
            'nickname'=>'user',
            'email'=>'user1@user1.com'), array('createdTime', 'DESC'), 0, 10);

        $foundUsers = $this->getUserService()->searchUsers(array(
            'roles'=>'ROLE_USER',
            'loginIp'=>'',
            'nickname'=>'user',
            'email'=>'user1@user1.com'), array('createdTime', 'DESC'), 0, 10);
    }


    public function testSearchUsersWithMultiParamterAndResultEqualsEmpty()
    {
        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');

        $foundUsers = $this->getUserService()->searchUsers(array(
            'nickname'=>'user1', 
            'roles'=>'ROLE_USER',
            'loginIp'=>'',
            'nickname'=>'user',
            'email'=>'user2@user2.com'), array('createdTime', 'DESC'), 0, 10);

        $foundUsers = $this->getUserService()->searchUsers(array(
            'nickname'=>'user2', 
            'roles'=>'ROLE_ADMIN',
            'loginIp'=>'',
            'nickname'=>'user',
            'email'=>'user1@user1.com'), array('createdTime', 'DESC'), 0, 10);

    }
    

    public function testSearchUserCount()
    {
        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');
        $foundUserCount = $this->getUserService()->searchUserCount(array('keywordType'=>'nickname','keyword'=>'user1'));
        $this->assertEquals(1, $foundUserCount);
        $foundUserCount = $this->getUserService()->searchUserCount(array('keywordType'=>'roles','keyword'=>'|ROLE_USER|'));
        $this->assertEquals(3, $foundUserCount);
        $foundUserCount = $this->getUserService()->searchUserCount(array('keywordType'=>'email','keyword'=>'user1@user1.com'));
    }

    public function testSearchUserCountWithZeroResult()
    {
        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');
        $foundUserCount = $this->getUserService()->searchUserCount(array('keywordType'=>'nickname','keyword'=>'not_exist_nickname'));
        $this->assertEquals(0, $foundUserCount);
        $foundUserCount = $this->getUserService()->searchUserCount(array('keywordType'=>'roles','keyword'=>'|ROLE_ADMIN|'));
        $this->assertEquals(0, $foundUserCount);
        $foundUserCount = $this->getUserService()->searchUserCount(array('keywordType'=>'email','keyword'=>'not_exist_email@user.com'));
        $this->assertEquals(0, $foundUserCount);
        $foundUserCount = $this->getUserService()->searchUserCount(array('keywordType'=>'loginIp','keyword'=>'192.168.0.1'));
        $this->assertEquals(0, $foundUserCount);
    }
    
    public function testSetEmailVerified()
    {
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->assertEquals(0, $registeredUser['emailVerified']);

        $this->getUserService()->setEmailVerified($registeredUser['id']);
        $foundUser = $this->getUserService()->getUser($registeredUser['id']);
        $this->assertEquals(1, $foundUser['emailVerified']);

        $this->getUserService()->setEmailVerified($registeredUser['id']);
        $foundUser = $this->getUserService()->getUser($registeredUser['id']);
        $this->assertEquals(1, $foundUser['emailVerified']);
    }

    public function testChangeEmail()
    {
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->changeEmail($registeredUser['id'], 'change@change.com');
        $foundUser = $this->getUserService()->getUser($registeredUser['id']);
        $this->assertEquals('change@change.com', $foundUser['email']);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testChangeEmailWithErrorEmailFormat1()
    {
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->changeEmail($registeredUser['id'], 'change@ch_ange.com');
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testChangeEmailWithErrorEmailFormat2()
    {
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->changeEmail($registeredUser['id'], 'changechange.com');
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testChangeEmailWithExistEmail()
    {
        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');
        $this->getUserService()->changeEmail($user1['id'], 'user2@user2.com');
    }

    public function testIsEmailAvaliable()
    {
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $this->getUserService()->register($userInfo);
        $result = $this->getUserService()->isEmailAvaliable('test@user.com');
        $this->assertTrue($result);
        $result = $this->getUserService()->isEmailAvaliable('test_email@email.com');
        $this->assertFalse($result);
        $result = $this->getUserService()->isEmailAvaliable('');
        $this->assertFalse($result);
    }

    public function testIsNicknameAvaliable()
    {
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $this->getUserService()->register($userInfo);

        $result = $this->getUserService()->isNicknameAvaliable('anothernickname');
        $this->assertTrue($result);
        $result = $this->getUserService()->isNicknameAvaliable('test_nickname');
        $this->assertFalse($result);
        $result = $this->getUserService()->isNicknameAvaliable('');
        $this->assertFalse($result);
    }

    public function testChangePassword()
    {
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->assertTrue($this->getUserService()->verifyPassword($registeredUser['id'], $userInfo['password']));

        $this->getUserService()->changePassword($registeredUser['id'], 'new_password');
        $changePasswordedUser = $this->getUserService()->getUser($registeredUser['id']);
        $this->assertTrue($this->getUserService()->verifyPassword($changePasswordedUser['id'], 'new_password'));
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testChangePasswordWithEmptyPassword()
    {
         $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->changePassword($registeredUser['id'], '');
    }
        
    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testChangePasswordWithNotExistUserId()
    {

        $this->getUserService()->changePassword(999, 'new_password');
    }
    
    public function testVerifyPassword()
    {
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->assertFalse($this->getUserService()->verifyPassword($registeredUser['id'], 'password'));
        $this->assertTrue($this->getUserService()->verifyPassword($registeredUser['id'], 'test_password'));
    }

     /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testVerifyPasswordWithNotExistUser()
    {
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->verifyPassword(999, 'password');
    }

    /**
     *  error
     */
    public function testFilterFollowingIds()
    {
        $fromUser = $this->createFromUser();
        $toUser = $this->createToUser();
        $followed = $this->getUserService()->follow($fromUser['id'], $toUser['id']);
        $followingIds = $this->getUserService()->filterFollowingIds($fromUser['id'], array(999, $toUser['id'], 777));
        $this->assertContains($toUser['id'], $followingIds);
    }

    public function testFollowOnce()
    {
        $fromUser = $this->createFromUser();
        $toUser = $this->createToUser();
        $followed = $this->getUserService()->follow($fromUser['id'], $toUser['id']);
        $this->assertEquals($fromUser['id'], $followed['fromId']);
        $this->assertEquals($toUser['id'], $followed['toId']);
    }

     /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testFollowNotExistUser()
    {
        $fromUser = $this->createFromUser();
        $this->getUserService()->follow($fromUser['id'], 999);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testFollowSelf()
    {
        $fromUser = $this->createFromUser();
        $this->getUserService()->follow($fromUser['id'], $fromUser['id']);
    }

    /**
     *  
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testFollowTwiceAndFailed()
    {
        $fromUser = $this->createFromUser();
        $toUser = $this->createToUser();
        $this->getUserService()->follow($fromUser['id'], $toUser['id']);
        $this->getUserService()->follow($fromUser['id'], $toUser['id']);
    }

    /**
     *  follow
     */
    public function testUnFollow()
    {
        $fromUser = $this->createFromUser();
        $toUser = $this->createToUser();
        $this->getUserService()->follow($fromUser['id'], $toUser['id']);
        $result = $this->getUserService()->unFollow($fromUser['id'], $toUser['id']);
        $this->assertEquals(1, $result);
    }
    
     /**
     *  follow
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testUnFollowNotExistUser()
    {
        $fromUser = $this->createFromUser();
        $toUser = $this->createToUser();
        $this->getUserService()->unFollow($fromUser['id'], 999);
    }

     /**
     *  follow
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testUnFollowWithoutFollowed()
    {
        $fromUser = $this->createFromUser();
        $toUser = $this->createToUser();
        $this->getUserService()->unFollow($fromUser['id'], $toUser['id']);
    }

    /**
     *   follow
     */
    public function testIsFollowed()
    {
        $fromUser = $this->createFromUser();
        $toUser = $this->createToUser();
        $this->assertFalse($this->getUserService()->isFollowed($fromUser['id'], $toUser['id']));

        $this->getUserService()->follow($fromUser['id'], $toUser['id']);
        $this->assertTrue($this->getUserService()->isFollowed($fromUser['id'], $toUser['id']));
    }

    /**
     *   follow
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testIsFollowWithNotExistToId()
    {
        $fromUser = $this->createFromUser();
        $this->getUserService()->isFollowed($fromUser['id'], 888);
    }

    /**
     *   follow
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testIsFollowWithNotExistFromId()
    {
        $toUser = $this->createToUser();
        $this->getUserService()->isFollowed(888, $toUser['id']);
    }

    /**
     *  profile
     */
    public function testGetUserProfile()
    {
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $userProfile = $this->getUserService()->getUserProfile($registeredUser['id']); 

        $this->assertEmpty($userProfile['truename']);
        $this->assertEquals('secret', $userProfile['gender']);
        $this->assertNull($userProfile['birthday']);
        $this->assertEmpty($userProfile['city']);
        $this->assertEmpty($userProfile['mobile']);
        $this->assertEmpty($userProfile['qq']);
        $this->assertEmpty($userProfile['signature']);
        $this->assertEmpty($userProfile['about']);
        $this->assertEmpty($userProfile['company']);
        $this->assertEmpty($userProfile['job']);
    }

    /**
     *  profile
     */
    public function testUpdateUserProfile()
    {
        $updateProfileInfo = array(
            'truename'=>'truename',
            'gender'=>'male',
            'birthday'=>'2013-01-01',
            'city'=>'10000',
            'mobile'=>'13888888888',
            'qq'=>'123456',
            'company'=>'company',
            'job'=>'job',
            'signature'=>'signature',
            'about'=>'about');

        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->updateUserProfile($registeredUser['id'], $updateProfileInfo);
        $userProfile = $this->getUserService()->getUserProfile($registeredUser['id']);

        $this->assertEquals($updateProfileInfo['truename'], $userProfile['truename']);
        $this->assertEquals($updateProfileInfo['gender'], $userProfile['gender']);
        $this->assertEquals($updateProfileInfo['birthday'], $userProfile['birthday']);
        $this->assertEquals($updateProfileInfo['city'], $userProfile['city']);
        $this->assertEquals($updateProfileInfo['mobile'], $userProfile['mobile']);
        $this->assertEquals($updateProfileInfo['qq'], $userProfile['qq']);
        $this->assertEquals($updateProfileInfo['signature'], $userProfile['signature']);
        $this->assertEquals($updateProfileInfo['about'], $userProfile['about']);
        $this->assertEquals($updateProfileInfo['job'], $userProfile['job']);
        $this->assertEquals($updateProfileInfo['company'], $userProfile['company']);
    }

    /**
     *  profile
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testUpdateUserProfileWithNotExistUser()
    {
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->updateUserProfile(999, array('gender'=>'male'));
    }

    /**
     *  profile
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testUpdateUserProfileWithErrorGender()
    {
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->updateUserProfile($registeredUser['id'], array('gender'=>'xxx'));
    }

    /**
     *  profile
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testUpdateUserProfileWithErrorBirthday()
    {
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->updateUserProfile($registeredUser['id'], array('birthday'=>'xxx'));
    }
    
    /**
     *  profile
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testUpdateUserProfileWithErrorMobile()
    {
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->updateUserProfile($registeredUser['id'], array('mobile'=>'8888'));
    }

    /**
     *  profile
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testUpdateUserProfileWithErrorQQ()
    {
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->updateUserProfile($registeredUser['id'], array('qq'=>'1'));
    }

    /**
     *  roles
     * 
     */
    public function testChangeUserRoles()
    {
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);

        $this->getUserService()->changeUserRoles($registeredUser['id'], array(
            'ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN','ROLE_TEACHER')
        );
        $foundUser = $this->getUserService()->getUser($registeredUser['id']);
        $this->assertEquals(array('ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER'), $foundUser['roles']);

        $this->getUserService()->changeUserRoles($registeredUser['id'], array(
            'ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN')
        );
        $foundUser = $this->getUserService()->getUser($registeredUser['id']);
        $this->assertEquals(array('ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN'), $foundUser['roles']);
    }


    /**
     *  roles
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testChangeUserRolesWithEmptyRoles()
    {
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->changeUserRoles($registeredUser['id'], array());
    }

    /**
     *  roles
     * @expectedException Topxia\Service\Common\ServiceException
     * 
     */
    public function testChangeUserRolesWithNotExistUser()
    {
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->changeUserRoles(999, array('ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN','ROLE_TEACHER'));
    }

    /**
     *  roles
     * @expectedException Topxia\Service\Common\ServiceException
     * 
     */
    public function testChangeUserRolesWithIllegalRoles()
    {
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->changeUserRoles($registeredUser['id'], array('ROLE_NOTEXIST_USER'));
    }

    /**
     *  token
     */
    public function testMakeToken()
    {
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $passwordRestToken = $this->getUserService()->makeToken('password-reset', $registeredUser['id'], 1371801141, 'password-reset-data');
        $emailVerifyToken = $this->getUserService()->makeToken('email-verify', $registeredUser['id'], 1371801141, 'data');
        $this->assertNotNull($passwordRestToken);
        $this->assertNotNull($emailVerifyToken);
    }

    /**
     *  token
     */
    public function testGetTokenSuccess()
    {
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $passwordRestToken = $this->getUserService()->makeToken('password-reset', $registeredUser['id'], strtotime('+1 day'), 'password-reset-data');
        $foundPasswordResetToken = $this->getUserService()->getToken('password-reset', $passwordRestToken);

        $this->assertEquals($registeredUser['id'], $foundPasswordResetToken['userId']);
        $this->assertEquals('password-reset', $foundPasswordResetToken['type']);
        $this->assertEquals('password-reset-data', $foundPasswordResetToken['data']);
    }

    /**
     *  token
     */
    public function testGetTokenFailedWithErrorTypeAndErrorToken()
    {
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $passwordRestToken = $this->getUserService()->makeToken('password-reset', $registeredUser['id'], 1371801141, 'password-reset-data');
        
        $foundPasswordResetToken = $this->getUserService()->getToken('password-reset', 'xxxxxxxxx');
        $this->assertNull($foundPasswordResetToken);

        $foundPasswordResetToken = $this->getUserService()->getToken('not_exist_tokenTyoe', $passwordRestToken);
        $this->assertNull($foundPasswordResetToken);

    }

    /**
     *  token
     */
    public function testGetTokenFailedWithExpiredTimeLessNow()
    {
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $passwordRestToken = $this->getUserService()->makeToken('password-reset', $registeredUser['id'], 1000, 'password-reset-data');
        
        $foundPasswordResetToken = $this->getUserService()->getToken('password-reset', $passwordRestToken);
        $this->assertNull($foundPasswordResetToken);
    }

    /**
     *  token
     */
    public function testDeleteToken()
    {
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $passwordRestToken = $this->getUserService()->makeToken('password-reset', $registeredUser['id'], 1000, 'password-reset-data');
        $deleteResult = $this->getUserService()->deleteToken('password-reset', $passwordRestToken);
        $this->assertTrue($deleteResult);
    }

    /**
     *  token
     */
    public function testDeleteTokenFailed()
    {
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $passwordRestToken = $this->getUserService()->makeToken('password-reset', $registeredUser['id'], 1000, 'password-reset-data');
        $deleteResult = $this->getUserService()->deleteToken('error_type', $passwordRestToken);
        $this->assertFalse($deleteResult);

        $deleteResult = $this->getUserService()->deleteToken('password-reset', 'error_token');
        $this->assertFalse($deleteResult);

        $deleteResult = $this->getUserService()->deleteToken('error_type', 'error_token');
        $this->assertFalse($deleteResult);
    }

    /**
     *  lock
     */
    public function testLockUser()
    {
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->assertEquals(0, $registeredUser['locked']);
        $this->getUserService()->lockUser($registeredUser['id']);
        $registeredUser = $this->getUserService()->getUser($registeredUser['id']);
        $this->assertEquals(1, $registeredUser['locked']);
    }

    /**
     *  lock
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testLockNotExistUser()
    {
        $this->getUserService()->lockUser(999);
    }

    /**
     *  lock
     */
    public function testUnLockUser()
    {
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->assertEquals(0, $registeredUser['locked']);
        $this->getUserService()->lockUser($registeredUser['id']);
        $this->getUserService()->unlockUser($registeredUser['id']);
        $registeredUser = $this->getUserService()->getUser($registeredUser['id']);
        $this->assertEquals(0, $registeredUser['locked']);
    }

    /**
     *  lock
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testUnLockNotExistUser()
    {
        $this->getUserService()->unlockUser(999);
    }

    /**
     *  bind
     */
    public function testBindUser()
    {
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $foundBind = $this->getUserService()->bindUser('qq',123123123, $registeredUser['id'], array('token'=>'token', 'expiredTime'=>strtotime('+1 day')));
        $this->assertEquals($registeredUser['id'], $foundBind['toId']);
    }

    /**
     *  bind
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testBindNotExistUser()
    {
        $this->getUserService()->bindUser('qq',123123123, 999, array('token'=>'token', 'expiredTime'=>strtotime('+1 day')));
    }

    /**
     *  bind
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testBindUserWithTypeNotInWeiboQQRenren()
    {
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $foundBind = $this->getUserService()->bindUser('douban',123123123, $registeredUser['id'], array('token'=>'token', 'expiredTime'=>strtotime('+1 day')));
        
    }

    /**
     *  bind
     */
    public function testGetUserBind()
    {
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->bindUser('qq',123123123, $registeredUser['id'], array('token'=>'token', 'expiredTime'=>strtotime('+1 day')));
        $foundBind = $this->getUserService()->getUserBindByTypeAndFromId('qq', 123123123);

        $this->assertEquals('qq', $foundBind['type']);
        $this->assertEquals(123123123, $foundBind['fromId']);
        $this->assertEquals($registeredUser['id'], $foundBind['toId']);
        $this->assertEquals('token', $foundBind['token']);
    }

    /**
     *  bind
     */
    public function testGetUserBindWithErrorType()
    {
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->bindUser('qq',123123123, $registeredUser['id'], array(
            'token'=>'token', 'expiredTime'=>strtotime('+1 day')));
        $this->getUserService()->getUserBindByTypeAndFromId('douban', 123123123);
    }

    /**
     *  bind
     */
    public function testGetUserBindWithErrorParamaters()
    {
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->bindUser('qq',123123123, $registeredUser['id'], array('token'=>'token', 'expiredTime'=>strtotime('+1 day')));
        $this->getUserService()->getUserBindByTypeAndFromId('qq', 7777);
        $this->getUserService()->getUserBindByTypeAndFromId('douban', 123123123);
    }
    
     /**
     *  bind
     */
    public function testGetUserBindWithExpiredTimeInvalidate()
    {
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->bindUser('qq',123123123, $registeredUser['id'], array('token'=>'token', 'expiredTime'=> 100));
        $this->getUserService()->getUserBindByTypeAndFromId('qq', 123123123);
    }

    /**
     *  bind
     */
    public function testGetUserBindByTypeAndUserId()
    {
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->bindUser('qq',123123123, $registeredUser['id'], array('token'=>'token', 'expiredTime'=> 100));
        $foundBind = $this->getUserService()->getUserBindByTypeAndUserId('qq',$registeredUser['id']);
        $this->assertEquals('qq', $foundBind['type']);
        $this->assertEquals(123123123, $foundBind['fromId']);
        $this->assertEquals($registeredUser['id'], $foundBind['toId']);
        $this->assertEquals('token', $foundBind['token']);
    }

    /**
     *  bind
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testGetUserBindWithInvalidateUserId()
    {
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->bindUser('qq',123123123, $registeredUser['id'], array('token'=>'token', 'expiredTime'=> 100));
        $this->getUserService()->getUserBindByTypeAndUserId('qq', 999);
    }

    /**
     *  bind
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testGetUserBindByTypeAndUserIdWithTypeNotInWeiboQQRenren()
    {
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->bindUser('qq',123123123, $registeredUser['id'], array('token'=>'token', 'expiredTime'=> 100));
        $this->getUserService()->getUserBindByTypeAndUserId('douban', $registeredUser['id']);
    }

    /**
     *  bind
     */
    public function testFindBindsByUserId()
    {
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->bindUser('qq',111111, $registeredUser['id'], array('token'=>'token', 'expiredTime'=> 100));
        $this->getUserService()->bindUser('renren',222222, $registeredUser['id'], array('token'=>'token', 'expiredTime'=> 100));
        $this->getUserService()->bindUser('weibo',333333, $registeredUser['id'], array('token'=>'token', 'expiredTime'=> 100));
        $userBinds = $this->getUserService()->findBindsByUserId($registeredUser['id']);
        $fromIds = array();
        foreach ($userBinds as $userBind) {
            array_push($fromIds, $userBind['fromId']);
        }
        $this->assertContains(111111, $fromIds);
        $this->assertContains(222222, $fromIds);
        $this->assertContains(333333, $fromIds);
    }

    /**
     *  bind
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testFindBindsByErrorUserId()
    {
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->bindUser('qq',111111, $registeredUser['id'], array('token'=>'token', 'expiredTime'=> 100));
        $this->getUserService()->bindUser('renren',222222, $registeredUser['id'], array('token'=>'token', 'expiredTime'=> 100));
        $this->getUserService()->bindUser('weibo',333333, $registeredUser['id'], array('token'=>'token', 'expiredTime'=> 100));
        $this->getUserService()->findBindsByUserId(999);
    }

    /**
     *  bind
     */
    public function testUnBindUserByTypeAndToId()
    {
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->bindUser('qq',111111, $registeredUser['id'], array('token'=>'token', 'expiredTime'=> 100));

        $result = $this->getUserService()->getUserBindByTypeAndUserId('qq', $registeredUser['id']);
        $this->assertNotNull($result);
        $this->getUserService()->unBindUserByTypeAndToId('qq', $registeredUser['id']);
        $result = $this->getUserService()->getUserBindByTypeAndUserId('qq', $registeredUser['id']);
        $this->assertFalse($result);
    }

     /**
     *  bind
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testUnBindUserByTypeAndToIdWithErrorUserId()
    {
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->bindUser('qq',111111, $registeredUser['id'], array('token'=>'token', 'expiredTime'=> 100));
        $this->getUserService()->unBindUserByTypeAndToId('qq', 999);
    }

    /**
     *  bind
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testUnBindUserByTypeAndToIdWithErrorType()
    {
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->bindUser('qq',111111, $registeredUser['id'], array('token'=>'token', 'expiredTime'=> 100));
        $this->getUserService()->unBindUserByTypeAndToId('douban', $registeredUser['id']);
    }

    private function createUser($user)
    {
        $userInfo = array();
        $userInfo['email'] = "{$user}@{$user}.com";
        $userInfo['nickname'] = "{$user}";
        $userInfo['password']= "{$user}";
        $userInfo['loginIp'] = '127.0.0.1';
        return $this->getUserService()->register($userInfo);
    }

    private function createFromUser()
    {
        $fromUser = array();
        $fromUser['email'] = 'fromUser@fromUser.com';
        $fromUser['nickname'] = 'fromUser';
        $fromUser['password']= 'fromUser';
        return $this->getUserService()->register($fromUser);
    }

    private function createToUser()
    {
        $toUser = array();
        $toUser['email'] = 'toUser@toUser.com';
        $toUser['nickname'] = 'toUser';
        $toUser['password']= 'toUser';
        return $this->getUserService()->register($toUser);
    }

    private function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

}