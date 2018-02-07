<?php

namespace Tests\Unit\User;

use Biz\BaseTestCase;
use Biz\User\CurrentUser;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

class UserServiceTest extends BaseTestCase
{
    public function testGetUserIdsByKeyword()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
            'verifiedMobile' => '13967340620',
            'mobile' => '13967340621',
        );
        $registeredUser1 = $this->getUserService()->register($userInfo);

        $userInfo = array(
            'nickname' => 'test_nickname1',
            'password' => 'test_password',
            'email' => 'edusoho@edusoho.com',
            'verifiedMobile' => '13967340622',
            'mobile' => '13967340623',
        );
        $registeredUser2 = $this->getUserService()->register($userInfo);

        $user = $this->getUserService()->getUserIdsByKeyword('test_nickname');
        $this->assertTrue(in_array($registeredUser1['id'], $user));

        $user = $this->getUserService()->getUserIdsByKeyword('test_email');
        $this->assertTrue(!in_array($registeredUser1['id'], $user));

        $user = $this->getUserService()->getUserIdsByKeyword('edusoho@edusoho.com');
        $this->assertTrue(in_array($registeredUser2['id'], $user));

        $user = $this->getUserService()->getUserIdsByKeyword('13967340622');
        $this->assertTrue(in_array($registeredUser2['id'], $user));

        $user = $this->getUserService()->getUserIdsByKeyword('13967340623');
        $this->assertTrue(in_array($registeredUser2['id'], $user));
    }

    /**
     * @group current
     */
    public function testRegister()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
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
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
            'token' => array('userId' => 999, 'token' => 'token', 'expiredTime' => strtotime('+1 day')),
            'type' => 'qq',
        );
        $registeredUser = $this->getUserService()->register($userInfo);

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
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testRegisterWithErrorEmail()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@error_email.com',
        );
        $this->getUserService()->register($userInfo);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testRegisterWithRegistedNickname()
    {
        $user1Info = array(
            'nickname' => 'testuser1',
            'password' => 'test_password',
            'email' => 'test_email@email1.com',
        );
        $this->getUserService()->register($user1Info);

        $user2Info = array(
            'nickname' => 'testuser1',
            'password' => 'test_password',
            'email' => 'test_email@email2.com',
        );
        $this->getUserService()->register($user2Info);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testRegisterWithRegistedEmail()
    {
        $user1Info = array(
            'nickname' => 'testuser1',
            'password' => 'test_password',
            'email' => 'test_email@registerdemail.com',
        );
        $this->getUserService()->register($user1Info);

        $user2Info = array(
            'nickname' => 'testuser2',
            'password' => 'test_password',
            'email' => 'test_email@registerdemail.com',
        );
        $this->getUserService()->register($user2Info);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testRegisterWithErrorNickname1()
    {
        $this->getUserService()->register(array(
            'nickname' => 'test_user nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        ));
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testRegisterWithErrorNickname2()
    {
        $this->getUserService()->register(array(
            'nickname' => 'user|!@2',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        ));
    }

    public function testGetUser()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
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
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $foundUser = $this->getUserService()->getUserByNickname($registeredUser['nickname']);
        $this->assertEquals($registeredUser, $foundUser);

        $foundUser = $this->getUserService()->getUserByNickname('not_exist_nickname');
        $this->assertNull($foundUser);
    }

    public function testGetUserByLoginField()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
            'verifiedMobile' => '13777868634',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $keyword = '13777868634';
        $result = $this->getUserService()->getUserByLoginField($keyword);
        $this->assertEquals($result['id'], $registeredUser['id']);
        $keyword = 'test_email@email.com';
        $result = $this->getUserService()->getUserByLoginField($keyword);
        $this->assertEquals($result['id'], $registeredUser['id']);
        $keyword = 'test_nickname';
        $result = $this->getUserService()->getUserByLoginField($keyword);
        $this->assertEquals($result['id'], $registeredUser['id']);
    }

    public function testGetUserByVerifiedMobile()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
            'verifiedMobile' => '13777868634',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $keyword = '13777868634';
        $result = $this->getUserService()->getUserByLoginField($keyword);
        $this->assertEquals($result['id'], $registeredUser['id']);
    }

    public function testGetUserByEmail()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
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

    /**
     * @group tmp
     */
    public function testSearchUsers()
    {
        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');

        $conditions = array(
            'nickname' => 'user1',
        );
        $orderBy = array('createdTime' => 'ASC');
        $result = $this->getUserService()->searchUsers($conditions, $orderBy, 0, 20);
        $this->assertEquals(1, count($result));
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
     * @group current
     */
    public function testSearchUsersWithOneParameter()
    {
        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');

        $foundUsers = $this->getUserService()->searchUsers(array('nickname' => 'user1'), array('createdTime' => 'DESC'), 0, 10);
        $this->assertEquals(1, count($foundUsers));

        $foundUsers = $this->getUserService()->searchUsers(array('roles' => 'ROLE_USER'), array('createdTime' => 'DESC'), 0, 10);
        // 还有一个初始化用户， admin@admin.com
        $this->assertEquals(3, count($foundUsers));

        $foundUsers = $this->getUserService()->searchUsers(array('loginIp' => ''), array('createdTime' => 'DESC'), 0, 10);
        // 还有一个初始化用户， admin@admin.com
        $this->assertEquals(3, count($foundUsers));

        $foundUsers = $this->getUserService()->searchUsers(array('nickname' => 'user'), array('createdTime' => 'DESC'), 0, 10);
        $this->assertEquals(2, count($foundUsers));

        $foundUsers = $this->getUserService()->searchUsers(array('email' => 'user1@user1.com'), array('createdTime' => 'DESC'), 0, 10);
        $this->assertEquals(1, count($foundUsers));

        $foundUsers = $this->getUserService()->searchUsers(array('email' => 'user2@user2.com'), array('createdTime' => 'DESC'), 0, 10);
        $this->assertEquals(1, count($foundUsers));
    }

    public function testSearchUsersWithOneParamterAndResultEqualsEmpty()
    {
        $foundUsers = $this->getUserService()->searchUsers(array('nickname' => 'user1'), array('createdTime' => 'DESC'), 0, 10);
        $this->assertEmpty($foundUsers);

        $foundUsers = $this->getUserService()->searchUsers(array('roles' => 'ROLE_USER'), array('createdTime' => 'DESC'), 0, 10);
        $this->assertEquals(1, count($foundUsers));

        $foundUsers = $this->getUserService()->searchUsers(array('loginIp' => ''), array('createdTime' => 'DESC'), 0, 10);
        $this->assertEquals(1, count($foundUsers));

        $foundUsers = $this->getUserService()->searchUsers(array('nickname' => 'user'), array('createdTime' => 'DESC'), 0, 10);
        $this->assertEquals(0, count($foundUsers));

        $foundUsers = $this->getUserService()->searchUsers(array('email' => 'user1@user1.com'), array('createdTime' => 'DESC'), 0, 10);
        $this->assertEquals(0, count($foundUsers));
    }

    public function testSearchUsersWithMultiParameter()
    {
        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');

        $foundUsers = $this->getUserService()->searchUsers(array(
            'nickname' => 'user1',
            'roles' => 'ROLE_USER',
            'loginIp' => '',
            'email' => 'user1@user1.com',
        ), array('createdTime' => 'DESC'), 0, 10);
        $this->assertEquals(1, count($foundUsers));

        $foundUsers = $this->getUserService()->searchUsers(array(
            'roles' => 'ROLE_USER',
            'loginIp' => '',
            'nickname' => 'user',
            'email' => 'user1@user1.com',
        ), array('createdTime' => 'DESC'), 0, 10);
        $this->assertEquals(1, count($foundUsers));
    }

    public function testSearchUsersWithMultiParameterAndResultEqualsEmpty()
    {
        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');

        $foundUsers = $this->getUserService()->searchUsers(array(
            'nickname' => 'user1',
            'roles' => 'ROLE_USER',
            'loginIp' => '',
            'email' => 'user2@user2.com',
        ), array('createdTime' => 'DESC'), 0, 10);
        $this->assertEquals(0, count($foundUsers));

        $foundUsers = $this->getUserService()->searchUsers(array(
            'nickname' => 'user2',
            'roles' => 'ROLE_ADMIN',
            'loginIp' => '',
            'email' => 'user1@user1.com',
        ), array('createdTime' => 'DESC'), 0, 10);
        $this->assertEquals(0, count($foundUsers));
    }

    /**
     * @group tmp
     */
    public function testcountUsers()
    {
        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');
        $foundUserCount = $this->getUserService()->countUsers(array('nickname' => 'user1'));
        $this->assertEquals(1, $foundUserCount);
        $foundUserCount = $this->getUserService()->countUsers(array('roles' => '|ROLE_USER|'));
        $this->assertEquals(3, $foundUserCount);
        $foundUserCount = $this->getUserService()->countUsers(array('email' => 'user1@user1.com'));
        $this->assertEquals(1, $foundUserCount);
    }

    public function testSearchUserCountWithZeroResult()
    {
        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');

        $foundUserCount = $this->getUserService()->countUsers(array('nickname' => 'not_exist_nickname'));
        $this->assertEquals(0, $foundUserCount);

        $currentUser = $this->getCurrentUser();
        $this->getUserService()->changeUserRoles($currentUser['id'], array('ROLE_USER'));
        $foundUserCount = $this->getUserService()->countUsers(array('roles' => '|ROLE_ADMIN|'));
        $this->assertEquals(0, $foundUserCount);

        $foundUserCount = $this->getUserService()->countUsers(array('email' => 'not_exist_email@user.com'));
        $this->assertEquals(0, $foundUserCount);
        $foundUserCount = $this->getUserService()->countUsers(array('loginIp' => '192.168.0.1'));
        $this->assertEquals(0, $foundUserCount);
    }

    public function testSetEmailVerified()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
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

    public function testChangeNickname()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->changeNickname($registeredUser['id'], 'hello123');
        $result = $this->getUserService()->getUser($registeredUser['id']);
        $this->assertEquals($result['nickname'], 'hello123');
    }

    /**
     * @expectedException  \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testChangeNicknameOne()
    {
        $user = null;
        $this->getUserService()->changeNickname($user['id'], 'hello123');
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testChangeNicknameTwo()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->changeNickname($registeredUser['id'], 'hell_!!o123');
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\AccessDeniedException
     */
    public function testChangeNicknameThree()
    {
        $user = $this->createUser('user');
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->changeNickname($registeredUser['id'], 'user');
    }

    public function testChangeEmail()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->changeEmail($registeredUser['id'], 'change@change.com');
        $foundUser = $this->getUserService()->getUser($registeredUser['id']);
        $this->assertEquals('change@change.com', $foundUser['email']);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testChangeEmailWithErrorEmailFormat1()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->changeEmail($registeredUser['id'], 'change@ch_ange.com');
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testChangeEmailWithErrorEmailFormat2()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->changeEmail($registeredUser['id'], 'changechange.com');
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\AccessDeniedException
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
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $user = $this->getUserService()->register($userInfo);
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
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $this->getUserService()->register($userInfo);

        $result = $this->getUserService()->isNicknameAvaliable('anothernickname');
        $this->assertTrue($result);
        $result = $this->getUserService()->isNicknameAvaliable('test_nickname');
        $this->assertFalse($result);
        $result = $this->getUserService()->isNicknameAvaliable('');
        $this->assertFalse($result);
    }

    public function testIsMobileAvaliable()
    {
        $result = $this->getUserService()->isMobileAvaliable('');
        $this->assertFalse($result);
        $result = $this->getUserService()->isMobileAvaliable('13777868634');
        $this->assertTrue($result);
    }

    public function testChangePassword()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->assertTrue($this->getUserService()->verifyPassword($registeredUser['id'], $userInfo['password']));

        $this->getUserService()->changePassword($registeredUser['id'], 'new_password');
        $changePasswordedUser = $this->getUserService()->getUser($registeredUser['id']);
        $this->assertTrue($this->getUserService()->verifyPassword($changePasswordedUser['id'], 'new_password'));
    }

    /**
     * @expectedException  \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testChangePasswordTwice()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->changePassword($registeredUser['id'], '');
    }

    /**
     * @expectedException  \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testChangePayPasswordOne()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->changePayPassword($registeredUser['id'], '');
    }

    public function testChangePayPasswordTwice()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $newPayPassword = '12345asd';
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->changePayPassword($registeredUser['id'], $newPayPassword);

        $user = $this->getUserService()->getUser($registeredUser['id']);

        $expectedPassword = $this->getPasswordEncoder()->encodePassword($newPayPassword, $user['payPasswordSalt']);
        $this->assertEquals($expectedPassword, $user['payPassword']);
    }

    public function testIsMobileUnique()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
            'verifiedMobile' => '13777868634',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $result = $this->getUserService()->isMobileUnique('13777868634');
        $this->assertFalse($result);
        $result = $this->getUserService()->isMobileUnique('18777868634');
        $this->assertTrue($result);
    }

    public function testChangeMobileOne()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
            'verifiedMobile' => '13777868634',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $result = $this->getUserService()->changeMobile($registeredUser['id'], '18257739598');
        $this->assertTrue($result);
    }

    /**
     * @expectedException  \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testChangeMobileTwice()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
            'verifiedMobile' => '13777868634',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $result = $this->getUserService()->changeMobile($registeredUser['id'], '');
    }

    public function testVerifyInSaltOut()
    {
        $in = 'test';
        $out = 'xw4L6lqFZ9b43YFhZKn73sOgZpK52o/GE60emMO4AUo=';
        $salt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $result = $this->getUserService()->verifyInSaltOut($in, $salt, $out);
        $this->assertFalse($result);
    }

    public function testVerifyPasswordOne()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->assertFalse($this->getUserService()->verifyPassword($registeredUser['id'], 'password'));
        $this->assertTrue($this->getUserService()->verifyPassword($registeredUser['id'], 'test_password'));
    }

    /**
     * @expectedException  \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testVerifyPayPasswordTwice()
    {
        $registeredUser = null;
        $this->assertFalse($this->getUserService()->verifyPassword($registeredUser['id'], 'password'));
    }

    public function testParseRegistration()
    {
        $auth['register_mode'] = 'email_or_mobile';
        $this->getSettingService()->set('auth', $auth);
        $registration['emailOrMobile'] = '627099747@qq.com';
        $result = $this->getUserService()->parseRegistration($registration);
        $this->assertEquals('627099747@qq.com', $result['emailOrMobile']);
    }

    public function testParseRegistrationTwice()
    {
        $auth['register_mode'] = 'email_or_mobile';
        $this->getSettingService()->set('auth', $auth);
        $registration['emailOrMobile'] = '13777777976';
        $result = $this->getUserService()->parseRegistration($registration);
        $this->assertEquals('13777777976', $result['mobile']);
    }

    /**
     * @expectedException  \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testParseRegistrationThird()
    {
        $auth['register_mode'] = 'email_or_mobile';
        $this->getSettingService()->set('auth', $auth);
        $registration['emailOrMobile'] = '';
        $this->getUserService()->parseRegistration($registration);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testParseRegistrationForth()
    {
        $auth['register_mode'] = 'email_or_mobile';
        $this->getSettingService()->set('auth', $auth);
        $registration['emailOrMobile'] = 'x';
        $this->getUserService()->parseRegistration($registration);
    }

    public function testParseRegistrationFifth()
    {
        $auth['register_mode'] = 'mobile';
        $this->getSettingService()->set('auth', $auth);
        $registration['mobile'] = '13777822976';
        $result = $this->getUserService()->parseRegistration($registration);
        $this->assertEquals('13777822976', $result['mobile']);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testParseRegistrationSixth()
    {
        $auth['register_mode'] = 'mobile';
        $this->getSettingService()->set('auth', $auth);
        $registration['mobile'] = 'z';
        $this->getUserService()->parseRegistration($registration);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testParseRegistrationSeventh()
    {
        $auth['register_mode'] = 'mobile';
        $this->getSettingService()->set('auth', $auth);
        $registration['mobile'] = 'x';
        $this->getUserService()->parseRegistration($registration);
    }

    public function testParseRegistrationEighth()
    {
        $auth['register_mode'] = '';
        $this->getSettingService()->set('auth', $auth);
        $registration = null;
        $parsedRegistration = $this->getUserService()->parseRegistration($registration);
        $this->assertEquals('web_email', $parsedRegistration['type']);
    }

    public function testIsMobileRegisterMode()
    {
        $auth['register_mode'] = 'mobile';
        $this->getSettingService()->set('auth', $auth);
        $result = $this->getUserService()->IsMobileRegisterMode();
        $this->assertTrue($result);
    }

    public function testGenerateNickname()
    {
        $userInfo = array(
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $nickname = $this->getUserService()->generateNickname($userInfo);
        $this->assertNotNull($nickname);
    }

    public function testGenerateEmail()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
        );
        $email = $this->getUserService()->generateEmail($userInfo);
        $this->assertNotNull($email);
    }

    /**
     * @expectedException  \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testChangePasswordWithEmptyPassword()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->changePassword($registeredUser['id'], '');
    }

    /**
     * @expectedException  \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testChangePasswordWithNotExistUserId()
    {
        $this->getUserService()->changePassword(0, 'new_password');
    }

    /**
     * @expectedException  \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testVerifyPasswordTwice()
    {
        $this->getUserService()->verifyPassword(0, 'password');
    }

    /**
     * @expectedException  \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testVerifyPasswordWithNotExistUser()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->verifyPassword(0, 'password');
    }

    /**
     *  error.
     */
    public function testFilterFollowingIds()
    {
        $fromUser = $this->createFromUser();
        $toUser = $this->createToUser();
        $followed = $this->getUserService()->follow($fromUser['id'], $toUser['id']);
        $followingIds = $this->getUserService()->filterFollowingIds($fromUser['id'], array(999, $toUser['id'], 777));
        $this->assertContains($toUser['id'], $followingIds);
    }

    public function testFollowOnce() //touser 是被关注者
    {
        $fromUser = $this->createFromUser();
        $toUser = $this->createToUser();
        $followed = $this->getUserService()->follow($fromUser['id'], $toUser['id']);
        $this->assertEquals($fromUser['id'], $followed['fromId']);
        $this->assertEquals($toUser['id'], $followed['toId']);
    }

    public function testFindUserFollowing()
    {
        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');
        $this->getUserService()->follow($user1['id'], $user2['id']);
        $result = $this->getUserService()->searchUserFollowings($user1['id'], 0, 20);
        $this->assertEquals(1, count($result));
    }

    public function testFindAllUserFollowing()
    {
        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');
        $user3 = $this->createUser('user3');
        $this->getUserService()->follow($user1['id'], $user3['id']);
        $this->getUserService()->follow($user1['id'], $user2['id']);
        $result = $this->getUserService()->searchUserFollowings($user1['id'], 0, 20);
        $this->assertEquals(2, count($result));
    }

    public function testFindUserFollowingCount()
    {
        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');
        $user3 = $this->createUser('user3');
        $this->getUserService()->follow($user1['id'], $user3['id']);
        $this->getUserService()->follow($user1['id'], $user2['id']);
        $result = $this->getUserService()->searchUserFollowings($user1['id'], 0, 20);
        $this->assertEquals(2, count($result));
    }

    public function testFindUserFollowers()
    {
        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');
        $user3 = $this->createUser('user3');
        $this->getUserService()->follow($user1['id'], $user3['id']);
        $this->getUserService()->follow($user2['id'], $user3['id']);
        $result = $this->getUserService()->searchUserFollowers($user3['id'], 0, 20);
        $this->assertEquals(2, count($result));
    }

    public function testFindAllUserFollower()
    {
        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');
        $user3 = $this->createUser('user3');
        $this->getUserService()->follow($user1['id'], $user3['id']);
        $this->getUserService()->follow($user2['id'], $user3['id']);
        $result = $this->getUserService()->searchUserFollowers($user3['id'], 0, 20);
        $this->assertEquals(2, count($result));
    }

    public function testFindUserFollowerCount()
    {
        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');
        $user3 = $this->createUser('user3');
        $this->getUserService()->follow($user1['id'], $user3['id']);
        $this->getUserService()->follow($user3['id'], $user1['id']);
        $result = $this->getUserService()->countUserFollowers($user1['id'], 0, 20);
        $this->assertEquals(1, count($result));
    }

    public function testFollow()
    {
        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');
        $user3 = $this->createUser('user3');
        $this->getUserService()->follow($user1['id'], $user3['id']);
        $this->getUserService()->follow($user2['id'], $user3['id']);
        $this->assertTrue($this->getUserService()->isFollowed($user1['id'], $user3['id']));
        $this->assertTrue($this->getUserService()->isFollowed($user2['id'], $user3['id']));
    }

    /**
     * @expectedException  \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testFollowTwice()
    {
        $user1 = $this->createUser('user1');
        $user2 = null;
        $this->getUserService()->follow($user1['id'], $user2['id']);
    }

    /**
     * @expectedException  \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testFollowThird()
    {
        $user1 = $this->createUser('user1');
        $this->getUserService()->follow($user1['id'], $user1['id']);
    }

    /**
     * @expectedException  \Codeages\Biz\Framework\Service\Exception\ServiceException
     */
    public function testFollowForth()
    {
        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');
        $this->getUserService()->follow($user1['id'], $user2['id']);
        $this->getUserService()->follow($user1['id'], $user2['id']);
    }

    public function testhasAdminRoles()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $result = $this->getUserService()->HasAdminRoles($registeredUser['id']);
        $this->assertFalse($result);
        $this->getUserService()->changeUserRoles($registeredUser['id'], array(
            'ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER',
        ));
        $result = $this->getUserService()->HasAdminRoles($registeredUser['id']);
        $this->assertTrue($result);
    }

    /**
     *  follow.
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
     * @expectedException  \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testUnFollowTwcie()
    {
        $user1 = $this->createUser('user1');
        $user2 = null;
        $this->getUserService()->unFollow($user1['id'], $user2['id']);
    }

    /**
     * @expectedException  \Codeages\Biz\Framework\Service\Exception\ServiceException
     */
    public function testUnFollowThird()
    {
        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');
        $this->getUserService()->unFollow($user1['id'], $user2['id']);
    }

    /**
     *  follow.
     *
     * @expectedException  \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testUnFollowNotExistUser()
    {
        $fromUser = $this->createFromUser();
        $toUser = $this->createToUser();
        $this->getUserService()->unFollow($fromUser['id'], 0);
    }

    /**
     *  follow.
     *
     * @expectedException  \Codeages\Biz\Framework\Service\Exception\ServiceException
     */
    public function testUnFollowWithoutFollowed()
    {
        $fromUser = $this->createFromUser();
        $toUser = $this->createToUser();
        $this->getUserService()->unFollow($fromUser['id'], $toUser['id']);
    }

    /**
     *   follow.
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
     *  follow.
     *
     * @expectedException  \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testIsFollowedTwice()
    {
        $user1 = null;
        $toUser = $this->createToUser();
        $this->getUserService()->isFollowed($user1['id'], $toUser['id']);
    }

    /**
     *  follow.
     *
     * @expectedException  \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testIsFollowedThird()
    {
        $fromUser = $this->createFromUser();
        $user2 = null;
        $this->getUserService()->isFollowed($fromUser['id'], $user2['id']);
    }

    public function testGetLastestApprovalByUserIdAndStatus()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $result = $this->getUserService()->getLastestApprovalByUserIdAndStatus($registeredUser['id'], 'approving');
        $this->assertFalse($result);
    }

    public function testfindUserApprovalsByUserIds()
    {
        $users = array();
        $result = $this->getUserService()->findUserApprovalsByUserIds($users);
        $this->assertEquals(0, count($result));
    }

    // public function testApplyUserApproval()//*

    // {

    // }

    /**
     * @expectedException  \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testApplyUserApprovalTwice()
    {
        $file = new \Symfony\Component\HttpFoundation\File\UploadedFile(
            __DIR__.'/Fixtures/test.gif',
            'original.gif',
            'image/gif',
            filesize(__DIR__.'/Fixtures/test.gif'),
            null
        );

        $userId = null;
        $approval = null;
        $faceImg = $file;
        $backImg = $file;
        $directory = null;
        $this->getUserService()->applyUserApproval($userId, $approval, $faceImg, $backImg, $directory);
    }

    /**
     * @expectedException  \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testPassApprovalTwice()
    {
        $user = null;
        $note = null;
        $this->getUserService()->passApproval($user['id'], $note);
    }

    public function testRejectApproval()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $result = $this->getUserService()->rejectApproval($registeredUser['id']);
        $this->assertTrue($result);
    }

    /**
     * @expectedException  \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testRejectApprovalTwice()
    {
        $user = null;
        $note = null;
        $this->getUserService()->rejectApproval($user['id'], $note);
    }

    // public function testDropFieldData()

    // {

    //     $fieldName = null;

    //     $this->getUserService()->dropFieldData($fie);
    // }

    public function testRememberLoginSessionIdOne()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $sessionId = '123.0.0.1';
        $this->getUserService()->rememberLoginSessionId($registeredUser['id'], $sessionId);
        $result = $this->getUserService()->getUser($registeredUser['id']);
        $this->assertNotNull($result['loginSessionId']);
    }

    /**
     * @expectedException  \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testRememberLoginSessionIdTwice()
    {
        $user = null;
        $sessionId = '123.0.0.1';
        $this->getUserService()->rememberLoginSessionId($user['id'], $sessionId);
    }

    public function testAnalysisRegisterDataByTime()
    {
        $time1 = time();
        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');
        $user3 = $this->createUser('user3');
        $time2 = time();
        $arrays = $this->getUserService()->analysisRegisterDataByTime($time1, $time2);
        $result = $arrays['0'];
        $this->assertGreaterThanOrEqual('3', $result['count']);
    }

    public function testParseAts()
    {
        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');
        $user3 = $this->createUser('user3');
        $text = '看我召唤三只猪!@user1,@user2,@user3,谢谢!';
        $result = $this->getUserService()->parseAts($text);
        $this->assertEquals(3, count($result));
    }

    /**
     *   follow.
     *
     * @expectedException  \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testIsFollowWithNotExistToId()
    {
        $fromUser = $this->createFromUser();
        $this->getUserService()->isFollowed($fromUser['id'], 888);
    }

    /**
     *   follow.
     *
     * @expectedException  \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testIsFollowWithNotExistFromId()
    {
        $toUser = $this->createToUser();
        $this->getUserService()->isFollowed(888, $toUser['id']);
    }

    /**
     *  profile.
     */
    public function testGetUserProfile()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
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
     *  profile.
     */
    public function testUpdateUserProfile()
    {
        $updateProfileInfo = array(
            'truename' => 'truename',
            'gender' => 'male',
            'birthday' => '2013-01-01',
            'city' => '10000',
            'mobile' => '13888888888',
            'qq' => '123456',
            'company' => 'company',
            'job' => 'job',
            'signature' => 'signature',
            'about' => 'about',
        );

        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
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
     *  profile.
     *
     * @expectedException  \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testUpdateUserProfileWithNotExistUser()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->updateUserProfile(999, array('gender' => 'male'));
    }

    /**
     *  profile.
     *
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testUpdateUserProfileWithErrorGender()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->updateUserProfile($registeredUser['id'], array('gender' => 'xxx'));
    }

    /**
     *  profile.
     *
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testUpdateUserProfileWithErrorBirthday()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->updateUserProfile($registeredUser['id'], array('birthday' => 'xxx'));
    }

    /**
     *  profile.
     *
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testUpdateUserProfileWithErrorMobile()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->updateUserProfile($registeredUser['id'], array('mobile' => '8888'));
    }

    /**
     *  profile.
     *
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testUpdateUserProfileWithErrorQQ()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->updateUserProfile($registeredUser['id'], array('qq' => '1'));
    }

    /**
     *  roles.
     */
    public function testChangeUserRoles()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);

        $this->getUserService()->changeUserRoles($registeredUser['id'], array(
            'ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER',
        )
        );
        $foundUser = $this->getUserService()->getUser($registeredUser['id']);
        $this->assertEquals(array('ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER'), $foundUser['roles']);

        $this->getUserService()->changeUserRoles($registeredUser['id'], array(
            'ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN',
        )
        );
        $foundUser = $this->getUserService()->getUser($registeredUser['id']);
        $this->assertEquals(array('ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN'), $foundUser['roles']);
    }

    /**
     *  roles.
     *
     * @expectedException  \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testChangeUserRolesWithEmptyRoles()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->changeUserRoles($registeredUser['id'], array());
    }

    /**
     *  roles.
     *
     * @expectedException  \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testChangeUserRolesWithNotExistUser()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->changeUserRoles(999, array('ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER'));
    }

    /**
     *  roles.
     *
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testChangeUserRolesWithIllegalRoles()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->changeUserRoles($registeredUser['id'], array('ROLE_NOTEXIST_USER'));
    }

    /**
     *  token.
     */
    public function testMakeToken()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $passwordRestToken = $this->getUserService()->makeToken('password-reset', $registeredUser['id'], 1371801141, 'password-reset-data');
        $emailVerifyToken = $this->getUserService()->makeToken('email-verify', $registeredUser['id'], 1371801141, 'data');
        $this->assertNotNull($passwordRestToken);
        $this->assertNotNull($emailVerifyToken);
    }

    public function testGetToken()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $passwordRestToken = $this->getUserService()->makeToken('password-reset', $registeredUser['id'], strtotime('+1 day'), 'password-reset-data');
        $foundPasswordResetToken = $this->getUserService()->getToken('password-reset', $passwordRestToken);
        $this->assertEquals($registeredUser['id'], $foundPasswordResetToken['userId']);
        $this->assertEquals('password-reset', $foundPasswordResetToken['type']);
        $this->assertEquals('password-reset-data', $foundPasswordResetToken['data']);
    }

    /**
     *  token.
     */
    public function testGetTokenSuccess()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $passwordRestToken = $this->getUserService()->makeToken('password-reset', $registeredUser['id'], strtotime('+1 day'), 'password-reset-data');
        $foundPasswordResetToken = $this->getUserService()->getToken('password-reset', $passwordRestToken);

        $this->assertEquals($registeredUser['id'], $foundPasswordResetToken['userId']);
        $this->assertEquals('password-reset', $foundPasswordResetToken['type']);
        $this->assertEquals('password-reset-data', $foundPasswordResetToken['data']);
    }

    /**
     *  token.
     */
    public function testGetTokenFailedWithErrorTypeAndErrorToken()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $passwordRestToken = $this->getUserService()->makeToken('password-reset', $registeredUser['id'], 1371801141, 'password-reset-data');

        $foundPasswordResetToken = $this->getUserService()->getToken('password-reset', 'xxxxxxxxx');
        $this->assertNull($foundPasswordResetToken);

        $foundPasswordResetToken = $this->getUserService()->getToken('not_exist_tokenTyoe', $passwordRestToken);
        $this->assertNull($foundPasswordResetToken);
    }

    /**
     *  token.
     */
    public function testGetTokenFailedWithExpiredTimeLessNow()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $passwordRestToken = $this->getUserService()->makeToken('password-reset', $registeredUser['id'], 1000, 'password-reset-data');

        $foundPasswordResetToken = $this->getUserService()->getToken('password-reset', $passwordRestToken);
        $this->assertNull($foundPasswordResetToken);
    }

    public function testSearchTokenCount()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $emailVerifyToken = $this->getUserService()->makeToken('email-verify', $registeredUser['id'], 1371801141, 'data');
        $result = $this->getUserService()->countTokens(array('type' => 'email-verify'));
        $this->assertEquals('1', $result);
    }

    /**
     *  token.
     */
    public function testDeleteToken()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $passwordRestToken = $this->getUserService()->makeToken('password-reset', $registeredUser['id'], 1000, 'password-reset-data');
        $deleteResult = $this->getUserService()->deleteToken('password-reset', $passwordRestToken);
        $this->assertTrue($deleteResult);
    }

    /**
     *  token.
     */
    public function testDeleteTokenFailed()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
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
     *  lock.
     */
    public function testLockUser()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->assertEquals(0, $registeredUser['locked']);
        $this->getUserService()->lockUser($registeredUser['id']);
        $registeredUser = $this->getUserService()->getUser($registeredUser['id']);
        $this->assertEquals(1, $registeredUser['locked']);
    }

    /**
     *   lock.
     *
     * @expectedException  \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testLockUserTwice()
    {
        $user = null;
        $this->getUserService()->lockUser($user['id']);
    }

    /**
     *  lock.
     *
     * @expectedException  \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testLockNotExistUser()
    {
        $this->getUserService()->lockUser(999);
    }

    /**
     *  lock.
     */
    public function testUnLockUser()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->assertEquals(0, $registeredUser['locked']);
        $this->getUserService()->lockUser($registeredUser['id']);
        $this->getUserService()->unlockUser($registeredUser['id']);
        $registeredUser = $this->getUserService()->getUser($registeredUser['id']);
        $this->assertEquals(0, $registeredUser['locked']);
    }

    /**
     * @expectedException  \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testUnLockUserTwice()
    {
        $user = null;
        $this->getUserService()->unlockUser($user);
    }

    public function testPromoteUser()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->promoteUser($registeredUser['id'], 1);
        $registeredUser = $this->getUserService()->getUser($registeredUser['id']);
        $this->assertEquals(1, $registeredUser['promoted']);
        $this->assertGreaterThan(0, $registeredUser['promotedTime']);
    }

    /**
     * @expectedException  \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testPromoteUserTwice()
    {
        $user = null;
        $this->getUserService()->promoteUser($user, 1);
    }

    public function testCancelPromoteUser()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->promoteUser($registeredUser['id'], 1);
        $registeredUser = $this->getUserService()->getUser($registeredUser['id']);
        $this->assertEquals(1, $registeredUser['promoted']);
        $this->getUserService()->cancelPromoteUser($registeredUser['id']);
        $registeredUser = $this->getUserService()->getUser($registeredUser['id']);
        $this->assertEquals(0, $registeredUser['promoted']);
    }

    /**
     * @expectedException  \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testCancelPromoteUserTwice()
    {
        $user = null;
        $this->getUserService()->cancelPromoteUser($user);
    }

    public function testFindLatestPromotedTeacher()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->changeUserRoles($registeredUser['id'], array(
            'ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER',
        ));
        $this->getUserService()->promoteUser($registeredUser['id'], 1);
        $result = $this->getUserService()->findLatestPromotedTeacher(0, 20);
        $result = $result['0'];
        $this->assertEquals($registeredUser['id'], $result['id']);
    }

    public function testWaveUserCounter()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->waveUserCounter($registeredUser['id'], 'newNotificationNum', 1);
        $foundUser = $this->getUserService()->getUser($registeredUser['id']);
        $this->assertEquals('1', $foundUser['newNotificationNum']);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testWaveUserCounterTwice()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->waveUserCounter($registeredUser['id'], 'newMessageNum', 'ss');
    }

    public function testClearUserCounter()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->waveUserCounter($registeredUser['id'], 'newMessageNum', 1);
        $registeredUser = $this->getUserService()->getUser($registeredUser['id']);
        $this->assertEquals('1', $registeredUser['newMessageNum']);
        $this->getUserService()->clearUserCounter($registeredUser['id'], 'newMessageNum');
        $registeredUser = $this->getUserService()->getUser($registeredUser['id']);
        $this->assertEquals('0', $registeredUser['newMessageNum']);
    }

    /**
     *  lock.
     *
     * @expectedException  \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testUnLockNotExistUser()
    {
        $this->getUserService()->unlockUser(999);
    }

    /**
     *  bind.
     */
    public function testBindUser()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->bindUser('qq', 123123123, $registeredUser['id'], array('token' => 'token', 'expiredTime' => strtotime('+1 day')));
        $user = $this->getUserService()->getUserBindByToken('token');
        $this->assertEquals($registeredUser['id'], $user['toId']);
    }

    public function testMarkLoginInfo()
    {
        $user = $this->biz['user'];
        $user['currentIp'] = '127.2.1.'.rand(1, 255);
        $this->biz['user'] = $user;
        $this->getUserService()->markLoginInfo();

        $dbUser = $this->getUserService()->getUser($user['id']);
        $this->assertEquals($user['currentIp'], $dbUser['loginIp']);
    }

    public function testMarkLoginFailed()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $ip = '127.0.0.1';
        $result = $this->getUserService()->markLoginFailed($registeredUser['id'], $ip);
        $this->assertNotNull($result);
    }

    public function testMarkLoginSuccess()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $ip = '152.0.1';
        $result = $this->getUserService()->markLoginSuccess($registeredUser['id'], $ip);
        $this->assertNull($result);
    }

    public function testCheckLoginForbidden()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $ip = '152.0.1';
        $result = $this->getUserService()->checkLoginForbidden($registeredUser['id'], $ip);
        $this->assertEquals('ok', $result['status']);
    }

    /**
     *  bind.
     *
     * @expectedException  \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testBindNotExistUser()
    {
        $this->getUserService()->bindUser('qq', 123123123, 999, array('token' => 'token', 'expiredTime' => strtotime('+1 day')));
    }

    /**
     *  bind.
     *
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testBindUserWithTypeNotInWeiboQQRenren()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $foundBind = $this->getUserService()->bindUser('douban', 123123123, $registeredUser['id'], array('token' => 'token', 'expiredTime' => strtotime('+1 day')));
    }

    /**
     *  bind.
     */
    public function testGetUserBind()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->bindUser('qq', 123123123, $registeredUser['id'], array('token' => 'token', 'expiredTime' => strtotime('+1 day')));
        $foundBind = $this->getUserService()->getUserBindByTypeAndFromId('qq', 123123123);

        $this->assertEquals('qq', $foundBind['type']);
        $this->assertEquals(123123123, $foundBind['fromId']);
        $this->assertEquals($registeredUser['id'], $foundBind['toId']);
        $this->assertEquals('token', $foundBind['token']);
    }

    /**
     *  bind.
     */
    public function testGetUserBindWithErrorType()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->bindUser('qq', 123123123, $registeredUser['id'], array(
            'token' => 'token', 'expiredTime' => strtotime('+1 day'),
        ));
        $userBinder = $this->getUserService()->getUserBindByTypeAndFromId('douban', 123123123);
        $this->assertEmpty($userBinder);
    }

    /**
     *  bind.
     */
    public function testGetUserBindWithErrorParamaters()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->bindUser('qq', 123123123, $registeredUser['id'], array('token' => 'token', 'expiredTime' => strtotime('+1 day')));
        $binderUser = $this->getUserService()->getUserBindByTypeAndFromId('qq', 7777);
        $this->assertNull($binderUser);
        $this->getUserService()->getUserBindByTypeAndFromId('douban', 123123123);
        $this->assertNull($binderUser);
    }

    /**
     *  bind.
     */
    public function testGetUserBindWithExpiredTimeInvalidate()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->bindUser('qq', 123123123, $registeredUser['id'], array('token' => 'token', 'expiredTime' => 100));
        $userInfo = $this->getUserService()->getUserBindByTypeAndFromId('qq', 123123123);

        $this->assertEquals('qq', $userInfo['type']);
        $this->assertEquals('token', $userInfo['token']);
    }

    /**
     *  bind.
     */
    public function testGetUserBindByTypeAndUserId()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->bindUser('qq', 123123123, $registeredUser['id'], array('token' => 'token', 'expiredTime' => 100));
        $foundBind = $this->getUserService()->getUserBindByTypeAndUserId('qq', $registeredUser['id']);
        $this->assertEquals('qq', $foundBind['type']);
        $this->assertEquals(123123123, $foundBind['fromId']);
        $this->assertEquals($registeredUser['id'], $foundBind['toId']);
        $this->assertEquals('token', $foundBind['token']);
    }

    /**
     *  bind.
     *
     * @expectedException  \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testGetUserBindByTypeAndUserIdTwice()
    {
        $registeredUser = null;
        $foundBind = $this->getUserService()->getUserBindByTypeAndUserId('qq', $registeredUser['id']);
    }

    /**
     *  bind.
     *
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testGetUserBindByTypeAndUserIdThird()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $type = null;
        $foundBind = $this->getUserService()->getUserBindByTypeAndUserId($type, $registeredUser['id']);
    }

    /**
     *  bind.
     *
     * @expectedException  \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testGetUserBindWithInvalidateUserId()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->bindUser('qq', 123123123, $registeredUser['id'], array('token' => 'token', 'expiredTime' => 100));
        $this->getUserService()->getUserBindByTypeAndUserId('qq', 999);
    }

    /**
     *  bind.
     *
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testGetUserBindByTypeAndUserIdWithTypeNotInWeiboQQRenren()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->bindUser('qq', 123123123, $registeredUser['id'], array('token' => 'token', 'expiredTime' => 100));
        $this->getUserService()->getUserBindByTypeAndUserId('douban', $registeredUser['id']);
    }

    /**
     *  bind.
     */
    public function testFindBindsByUserIdOne()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->bindUser('qq', 111111, $registeredUser['id'], array('token' => 'token', 'expiredTime' => 100));
        $this->getUserService()->bindUser('weibo', 333333, $registeredUser['id'], array('token' => 'token', 'expiredTime' => 100));
        $userBinds = $this->getUserService()->findBindsByUserId($registeredUser['id']);
        $fromIds = array();

        foreach ($userBinds as $userBind) {
            array_push($fromIds, $userBind['fromId']);
        }

        $this->assertContains(111111, $fromIds);
        $this->assertContains(333333, $fromIds);
    }

    /**
     *  bind.
     *
     * @expectedException  \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testFindBindsByUserIdTwice()
    {
        $user = null;
        $this->getUserService()->findBindsByUserId($user['id']);
    }

    /**
     *  bind.
     *
     * @expectedException  \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testFindBindsByErrorUserId()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->bindUser('qq', 111111, $registeredUser['id'], array('token' => 'token', 'expiredTime' => 100));
        $this->getUserService()->bindUser('weibo', 333333, $registeredUser['id'], array('token' => 'token', 'expiredTime' => 100));
        $binders = $this->getUserService()->findBindsByUserId(999);

        $this->assertEquals(2, count($binders));
    }

    /**
     *  @group tmp
     */
    public function testUnBindUserByTypeAndToIdOne()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->bindUser('qq', 111111, $registeredUser['id'], array('token' => 'token', 'expiredTime' => 100));

        $result = $this->getUserService()->getUserBindByTypeAndUserId('qq', $registeredUser['id']);
        $this->assertNotNull($result);
        $this->getUserService()->unBindUserByTypeAndToId('qq', $registeredUser['id']);
        $result = $this->getUserService()->getUserBindByTypeAndUserId('qq', $registeredUser['id']);
        $this->assertEmpty($result);
    }

    /**
     *  bind.
     *
     * @expectedException  \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testUnBindUserByTypeAndToIdTwice()
    {
        $type = null;
        $user = null;
        $this->getUserService()->unBindUserByTypeAndToId($type, $user['id']);
    }

    /**
     *  bind.
     *
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testUnBindUserByTypeAndToIdThird()
    {
        $type = null;
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->unBindUserByTypeAndToId($type, $registeredUser['id']);
    }

    public function testGetUserBindByTypeAndFromId()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->bindUser('qq', 123123123, $registeredUser['id'], array('token' => 'token', 'expiredTime' => strtotime('+1 day')));
        $foundBind = $this->getUserService()->getUserBindByTypeAndFromId('qq', 123123123);
        $this->assertEquals('qq', $foundBind['type']);
    }

    //用户银行校验
    public function testCreateUserPayAgreement()
    {
        $field = array('userId' => 1, 'type' => 0, 'bankName' => '农业银行', 'bankNumber' => 1124, 'bankAuth' => '0eeeee', 'bankId' => 1);
        $bank = $this->getUserService()->createUserPayAgreement($field);
        $this->assertEquals('农业银行', $bank['bankName']);
    }

    public function testGetUserPayAgreement()
    {
        $field = array('userId' => 1, 'type' => 0, 'bankName' => '农业银行', 'bankNumber' => 1124, 'bankAuth' => '0eeeee', 'bankId' => 1);
        $bank = $this->getUserService()->createUserPayAgreement($field);
        $authBank = $this->getUserService()->getUserPayAgreement($bank['id']);
        $this->assertEquals('农业银行', $authBank['bankName']);
    }

    public function testGetUserPayAgreementByUserIdAndBankAuth()
    {
        $field = array('userId' => 1, 'type' => 0, 'bankName' => '农业银行', 'bankNumber' => 1124, 'bankAuth' => '0eeeee', 'bankId' => 1);
        $bank = $this->getUserService()->createUserPayAgreement($field);
        $authBank = $this->getUserService()->getUserPayAgreementByUserIdAndBankAuth(1, '0eeeee');
        $this->assertEquals('农业银行', $authBank['bankName']);
    }

    public function testGetUserPayAgreementByUserId()
    {
        $field = array('userId' => 1, 'type' => 0, 'bankName' => '农业银行', 'bankNumber' => 1124, 'bankAuth' => '0eeeee', 'bankId' => 1);
        $bank = $this->getUserService()->createUserPayAgreement($field);
        $authBank = $this->getUserService()->getUserPayAgreementByUserId(1);
        $this->assertEquals('农业银行', $authBank['bankName']);
    }

    public function testUpdateUserPayAgreementByUserIdAndBankAuth()
    {
        $field = array('userId' => 1, 'type' => 0, 'bankName' => '农业银行', 'bankNumber' => 1124, 'bankAuth' => '0eeeee', 'bankId' => 1);
        $bank = $this->getUserService()->createUserPayAgreement($field);
        $authBank = $this->getUserService()->updateUserPayAgreementByUserIdAndBankAuth(1, '0eeeee', array('bankName' => '招商银行'));
        $this->assertEquals(1, 1);
    }

    public function testFindUserPayAgreementsByUserId()
    {
        $field = array('userId' => 1, 'type' => 0, 'bankName' => '农业银行', 'bankNumber' => 1124, 'bankAuth' => '0eeeee', 'bankId' => 1);
        $bank = $this->getUserService()->createUserPayAgreement($field);
        $authBank = $this->getUserService()->findUserPayAgreementsByUserId(1);
        $this->assertEquals('农业银行', $authBank[0]['bankName']);
    }

    public function testDeleteUserPayAgreements()
    {
        $field = array('userId' => 1, 'type' => 0, 'bankName' => '农业银行', 'bankNumber' => 1124, 'bankAuth' => '0eeeee', 'bankId' => 1);
        $bank = $this->getUserService()->createUserPayAgreement($field);
        $userPayAgreements = $this->getUserService()->deleteUserPayAgreements(1);
        $this->assertEquals(1, $userPayAgreements);
    }

    /**
     *  bind.
     *
     * @expectedException  \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testUnBindUserByTypeAndToIdWithErrorUserId()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->bindUser('qq', 111111, $registeredUser['id'], array('token' => 'token', 'expiredTime' => 100));
        $this->getUserService()->unBindUserByTypeAndToId('qq', 999);
    }

    /**
     *  bind.
     *
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testUnBindUserByTypeAndToIdWithErrorType()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->bindUser('qq', 111111, $registeredUser['id'], array('token' => 'token', 'expiredTime' => 100));
        $this->getUserService()->unBindUserByTypeAndToId('douban', $registeredUser['id']);
    }

    public function testGenerateNickname_prefix()
    {
        $user = $this->createUser('adminabc');
        $user['nickname'] = 'admin';
        $nickname = $this->getUserService()->generateNickname($user);
        $this->assertEquals(stripos($nickname, 'admin'), 0);
    }

    public function testGenerateNickname_specialChar()
    {
        $this->createUser('abcefg');
        $user['nickname'] = '🐎abcefg✈🐯️';
        $nickname = $this->getUserService()->generateNickname($user);
        $this->assertEquals(stripos($nickname, 'abcefg'), 0);
    }

    public function testGenerateNickname_emptyRaw()
    {
        $user = array();
        $nickname = $this->getUserService()->generateNickname($user);
        $this->assertEquals(stripos($nickname, 'user'), 0);
    }

    public function testUpdateUserNewMessageNum()
    {
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 2,
            'nickname' => 'admin1',
            'email' => 'admin3@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER', 'ROLE_ADMIN'),
        ));
        $currentUser->__set('newMessageNum', 2);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $this->mockBiz(
            'User:UserDao',
            array(
                array(
                    'functionName' => 'update',
                    'withParams' => array(2, array('newMessageNum' => 1)),
                ),
            )
        );
        $result = $this->getUserService()->updateUserNewMessageNum(2, 1);
        $this->assertNull($result);
    }

    public function testGetSmsCaptchaStatusWithLowProtective()
    {
        $settingService = $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'withParams' => array('auth', array()),
                    'returnValue' => array(
                        'register_mode' => 'mobile',
                        'register_protective' => 'low',
                    ),
                ),
            )
        );

        $this->assertEquals(
            'captchaIgnored',
            $this->getUserService()->getSmsCaptchaStatus('128.3.2.1', false)
        );

        $this->assertEquals(
            'captchaIgnored',
            $this->getUserService()->getSmsCaptchaStatus('128.3.2.1', true)
        );

        $this->assertEquals(
            'captchaRequired',
            $this->getUserService()->getSmsCaptchaStatus('128.3.2.1', false)
        );

        $settingService->shouldHaveReceived('get')->times(3);
    }

    public function testGetSmsCaptchaStatusWithNoneProtective()
    {
        $settingService = $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'withParams' => array('auth', array()),
                    'returnValue' => array(
                        'register_mode' => 'mobile',
                        'register_protective' => 'none',
                    ),
                ),
            )
        );

        $this->assertEquals(
            'captchaIgnored',
            $this->getUserService()->getSmsCaptchaStatus('128.3.2.1', false)
        );

        $this->assertEquals(
            'captchaIgnored',
            $this->getUserService()->getSmsCaptchaStatus('128.3.2.1', true)
        );

        $this->assertEquals(
            'captchaIgnored',
            $this->getUserService()->getSmsCaptchaStatus('128.3.2.1', false)
        );
        $settingService->shouldHaveReceived('get')->times(3);
    }

    public function testGetSmsCaptchaStatusWithNoneMobile()
    {
        $settingService = $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'withParams' => array('auth', array()),
                    'returnValue' => array(
                        'register_mode' => 'email',
                        'register_protective' => 'high',
                    ),
                ),
            )
        );

        $this->assertEquals(
            'smsUnsendable',
            $this->getUserService()->updateSmsRegistrationCaptchaCode('128.3.2.1')
        );

        $this->assertEquals(
            'smsUnsendable',
            $this->getUserService()->updateSmsRegistrationCaptchaCode('128.3.2.1')
        );

        $settingService->shouldHaveReceived('get')->times(2);
    }

    public function testUpdateSmsRegistrationCaptchaCode()
    {
        $settingService = $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'withParams' => array('auth', array()),
                    'returnValue' => array(
                        'register_mode' => 'mobile',
                        'register_protective' => 'high',
                    ),
                ),
            )
        );

        $this->assertEquals(
            'captchaRequired',
            $this->getUserService()->getSmsCaptchaStatus('128.3.2.1', false)
        );

        $this->assertEquals(
            'captchaRequired',
            $this->getUserService()->getSmsCaptchaStatus('128.3.2.1', true)
        );

        $settingService->shouldHaveReceived('get')->times(2);
    }

    protected function createUser($user)
    {
        $userInfo = array();
        $userInfo['email'] = "{$user}@{$user}.com";
        $userInfo['nickname'] = "{$user}";
        $userInfo['password'] = "{$user}";
        $userInfo['loginIp'] = '127.0.0.1';

        return $this->getUserService()->register($userInfo);
    }

    protected function createFromUser()
    {
        $fromUser = array();
        $fromUser['email'] = 'fromUser@fromUser.com';
        $fromUser['nickname'] = 'fromUser';
        $fromUser['password'] = 'fromUser';

        return $this->getUserService()->register($fromUser);
    }

    protected function createToUser()
    {
        $toUser = array();
        $toUser['email'] = 'toUser@toUser.com';
        $toUser['nickname'] = 'toUser';
        $toUser['password'] = 'toUser';

        return $this->getUserService()->register($toUser);
    }

    private function initFile()
    {
        $groups = $this->getFileService()->getAllFileGroups();

        foreach ($groups as $group) {
            $this->getFileService()->deleteFileGroup($group['id']);
        }

        $this->getFileService()->addFileGroup(array(
            'name' => '默认文件组',
            'code' => 'default',
            'public' => 1,
        ));

        $this->getFileService()->addFileGroup(array(
            'name' => '缩略图',
            'code' => 'thumb',
            'public' => 1,
        ));

        $this->getFileService()->addFileGroup(array(
            'name' => '课程',
            'code' => 'course',
            'public' => 1,
        ));

        $this->getFileService()->addFileGroup(array(
            'name' => '用户',
            'code' => 'user',
            'public' => 1,
        ));

        $this->getFileService()->addFileGroup(array(
            'name' => '课程私有文件',
            'code' => 'course_private',
            'public' => 0,
        ));

        $this->getFileService()->addFileGroup(array(
            'name' => '资讯',
            'code' => 'article',
            'public' => 1,
        ));

        $this->getFileService()->addFileGroup(array(
            'name' => '临时目录',
            'code' => 'tmp',
            'public' => 1,
        ));

        $this->getFileService()->addFileGroup(array(
            'name' => '全局设置文件',
            'code' => 'system',
            'public' => 1,
        ));

        $this->getFileService()->addFileGroup(array(
            'name' => '小组',
            'code' => 'group',
            'public' => 1,
        ));

        $this->getFileService()->addFileGroup(array(
            'name' => '编辑区',
            'code' => 'block',
            'public' => 1,
        ));

        $this->getFileService()->addFileGroup(array(
            'name' => '班级',
            'code' => 'classroom',
            'public' => 1,
        ));
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function getFileService()
    {
        return $this->createService('Content:FileService');
    }

    protected function getPasswordEncoder()
    {
        return new MessageDigestPasswordEncoder('sha256');
    }
}
