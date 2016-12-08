<?php

namespace Topxia\Service\User\Tests;

use Topxia\Service\Common\BaseTestCase;

class UserServiceTest extends BaseTestCase
{
    /**
     * @group current
     */
    public function testRegister()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
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
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com',
            'token'    => array('userId' => 999, 'token' => 'token', 'expiredTime' => strtotime('+1 day'))
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
     * @expectedException \Topxia\Common\Exception\UnexpectedValueException
     */
    public function testRegisterWithErrorEmail()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@error_email.com'
        );
        $this->getUserService()->register($userInfo);
    }

    /**
     * @expectedException \Topxia\Common\Exception\UnexpectedValueException
     */
    public function testRegisterWithRegistedNickname()
    {
        $user1Info = array(
            'nickname' => 'testuser1',
            'password' => 'test_password',
            'email'    => 'test_email@email1.com'
        );
        $this->getUserService()->register($user1Info);

        $user2Info = array(
            'nickname' => 'testuser1',
            'password' => 'test_password',
            'email'    => 'test_email@email2.com'
        );
        $this->getUserService()->register($user2Info);
    }

    /**
     * @expectedException \Topxia\Common\Exception\UnexpectedValueException
     */
    public function testRegisterWithRegistedEmail()
    {
        $user1Info = array(
            'nickname' => 'testuser1',
            'password' => 'test_password',
            'email'    => 'test_email@registerdemail.com'
        );
        $this->getUserService()->register($user1Info);

        $user2Info = array(
            'nickname' => 'testuser2',
            'password' => 'test_password',
            'email'    => 'test_email@registerdemail.com'
        );
        $this->getUserService()->register($user2Info);
    }

    /**
     * @expectedException \Topxia\Common\Exception\UnexpectedValueException
     */
    public function testRegisterWithErrorNickname1()
    {
        $this->getUserService()->register(array(
            'nickname' => 'test_user nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        ));
    }

    /**
     * @expectedException \Topxia\Common\Exception\UnexpectedValueException
     */
    public function testRegisterWithErrorNickname2()
    {
        $this->getUserService()->register(array(
            'nickname' => 'user|!@2',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        ));
    }

    public function testGetUser()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $foundUser      = $this->getUserService()->getUser($registeredUser['id']);
        $this->assertEquals($registeredUser, $foundUser);

        $foundUser = $this->getUserService()->getUser(999);
        $this->assertNull($foundUser);
    }

    public function testGetUserByNickname()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $foundUser      = $this->getUserService()->getUserByNickname($registeredUser['nickname']);
        $this->assertEquals($registeredUser, $foundUser);

        $foundUser = $this->getUserService()->getUserByNickname('not_exist_nickname');
        $this->assertNull($foundUser);
    }

    public function testGetUserByLoginField()
    {
        $userInfo       = array(
            'nickname'       => 'test_nickname',
            'password'       => 'test_password',
            'email'          => 'test_email@email.com',
            'verifiedMobile' => '13777868634'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $keyword        = '13777868634';
        $result         = $this->getUserService()->getUserByLoginField($keyword);
        $this->assertEquals($result['id'], $registeredUser['id']);
        $keyword = 'test_email@email.com';
        $result  = $this->getUserService()->getUserByLoginField($keyword);
        $this->assertEquals($result['id'], $registeredUser['id']);
        $keyword = 'test_nickname';
        $result  = $this->getUserService()->getUserByLoginField($keyword);
        $this->assertEquals($result['id'], $registeredUser['id']);
    }

    public function testGetUserByVerifiedMobile()
    {
        $userInfo       = array(
            'nickname'       => 'test_nickname',
            'password'       => 'test_password',
            'email'          => 'test_email@email.com',
            'verifiedMobile' => '13777868634'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $keyword        = '13777868634';
        $result         = $this->getUserService()->getUserByLoginField($keyword);
        $this->assertEquals($result['id'], $registeredUser['id']);
    }

    public function testGetUserByEmail()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $foundUser      = $this->getUserService()->getUserByEmail('test_email@email.com');
        $this->assertEquals($registeredUser, $foundUser);

        $foundUser = $this->getUserService()->getUserByEmail('not_exist_email@user.com');
        $this->assertNull($foundUser);
    }

    public function testFindUsersByIds()
    {
        $user1      = $this->createUser('user1');
        $user2      = $this->createUser('user2');
        $user3      = $this->createUser('user3');
        $foundUsers = $this->getUserService()->findUsersByIds(array($user1['id'], $user2['id']));

        $foundUserIds = array_keys($foundUsers);
        $this->assertEquals(2, count($foundUserIds));
        $this->assertContains($user1['id'], $foundUserIds);
        $this->assertContains($user2['id'], $foundUserIds);

        $foundUsers   = $this->getUserService()->findUsersByIds(array($user1['id'], $user2['id'], 99999));
        $foundUserIds = array_keys($foundUsers);

        $this->assertEquals(2, count($foundUserIds));
        $this->assertContains($user1['id'], $foundUserIds);
        $this->assertContains($user2['id'], $foundUserIds);
        $this->assertNotContains(99999, $foundUserIds);

        $foundUsers = $this->getUserService()->findUsersByIds(array(99999));
        $this->assertEmpty($foundUsers);
    }

    public function testSearchUsers()
    {
        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');

        $conditions = array(
            'nickname' => 'user1'
        );
        $orderBy    = array('createdTime', 'ASC');
        $result     = $this->getUserService()->SearchUsers($conditions, $orderBy, 0, 20);
        $this->assertEquals(1, count($result));
    }

    public function testFindUserProfilesByIds()
    {
        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');

        $foundUserProfiles = $this->getUserService()->findUserProfilesByIds(array($user1['id'], $user2['id']));
        $userProfileIds    = array_keys($foundUserProfiles);
        $this->assertEquals(2, count($foundUserProfiles));
        $this->assertContains($user1['id'], $userProfileIds);
        $this->assertContains($user2['id'], $userProfileIds);

        $foundUserProfiles = $this->getUserService()->findUserProfilesByIds(array($user1['id'], $user2['id'], 999));
        $userProfileIds    = array_keys($foundUserProfiles);
        $this->assertEquals(2, count($foundUserProfiles));
        $this->assertContains($user1['id'], $userProfileIds);
        $this->assertContains($user2['id'], $userProfileIds);

        $foundUserProfiles = $this->getUserService()->findUserProfilesByIds(array(999));
        $this->assertEmpty($foundUserProfiles);
    }

    /**
     * @group current
     */
    public function testSearchUsersWithOneParamter()
    {
        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');

        $foundUsers = $this->getUserService()->searchUsers(array('nickname' => 'user1'), array('createdTime', 'DESC'), 0, 10);

        $foundUsers = $this->getUserService()->searchUsers(array('roles' => 'ROLE_USER'), array('createdTime', 'DESC'), 0, 10);

        $foundUsers = $this->getUserService()->searchUsers(array('loginIp' => ''), array('createdTime', 'DESC'), 0, 10);

        $foundUsers = $this->getUserService()->searchUsers(array('nickname' => 'user'), array('createdTime', 'DESC'), 0, 10);

        $foundUsers = $this->getUserService()->searchUsers(array('email' => 'user1@user1.com'), array('createdTime', 'DESC'), 0, 10);

        $foundUsers = $this->getUserService()->searchUsers(array('email' => 'user2@user2.com'), array('createdTime', 'DESC'), 0, 10);
    }

    public function testSearchUsersWithOneParamterAndResultEqualsEmpty()
    {
        $foundUsers = $this->getUserService()->searchUsers(array('nickname' => 'user1'), array('createdTime', 'DESC'), 0, 10);
        $this->assertEmpty($foundUsers);

        $foundUsers = $this->getUserService()->searchUsers(array('roles' => 'ROLE_USER'), array('createdTime', 'DESC'), 0, 10);

        $foundUsers = $this->getUserService()->searchUsers(array('loginIp' => ''), array('createdTime', 'DESC'), 0, 10);

        $foundUsers = $this->getUserService()->searchUsers(array('nickname' => 'user'), array('createdTime', 'DESC'), 0, 10);

        $foundUsers = $this->getUserService()->searchUsers(array('email' => 'user1@user1.com'), array('createdTime', 'DESC'), 0, 10);
    }

    public function testSearchUsersWithMultiParamter()
    {
        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');

        $foundUsers = $this->getUserService()->searchUsers(array(
            'nickname' => 'user1',
            'roles'    => 'ROLE_USER',
            'loginIp'  => '',
            'nickname' => 'user',
            'email'    => 'user1@user1.com'
        ), array('createdTime', 'DESC'), 0, 10);

        $foundUsers = $this->getUserService()->searchUsers(array(
            'roles'    => 'ROLE_USER',
            'loginIp'  => '',
            'nickname' => 'user',
            'email'    => 'user1@user1.com'
        ), array('createdTime', 'DESC'), 0, 10);
    }

    public function testSearchUsersWithMultiParamterAndResultEqualsEmpty()
    {
        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');

        $foundUsers = $this->getUserService()->searchUsers(array(
            'nickname' => 'user1',
            'roles'    => 'ROLE_USER',
            'loginIp'  => '',
            'nickname' => 'user',
            'email'    => 'user2@user2.com'
        ), array('createdTime', 'DESC'), 0, 10);

        $foundUsers = $this->getUserService()->searchUsers(array(
            'nickname' => 'user2',
            'roles'    => 'ROLE_ADMIN',
            'loginIp'  => '',
            'nickname' => 'user',
            'email'    => 'user1@user1.com'
        ), array('createdTime', 'DESC'), 0, 10);
    }

    public function testSearchUserCount()
    {
        $user1          = $this->createUser('user1');
        $user2          = $this->createUser('user2');
        $foundUserCount = $this->getUserService()->searchUserCount(array('keywordType' => 'nickname', 'keyword' => 'user1'));
        $this->assertEquals(1, $foundUserCount);
        $foundUserCount = $this->getUserService()->searchUserCount(array('keywordType' => 'roles', 'keyword' => '|ROLE_USER|'));
        $this->assertEquals(3, $foundUserCount);
        $foundUserCount = $this->getUserService()->searchUserCount(array('keywordType' => 'email', 'keyword' => 'user1@user1.com'));
    }

    public function testSearchUserCountWithZeroResult()
    {
        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');

        $foundUserCount = $this->getUserService()->searchUserCount(array('keywordType' => 'nickname', 'keyword' => 'not_exist_nickname'));
        $this->assertEquals(0, $foundUserCount);

        $currentUser = $this->getCurrentUser();
        $this->getUserService()->changeUserRoles($currentUser['id'], array('ROLE_USER'));
        $foundUserCount = $this->getUserService()->searchUserCount(array('keywordType' => 'roles', 'keyword' => '|ROLE_ADMIN|'));
        $this->assertEquals(0, $foundUserCount);

        $foundUserCount = $this->getUserService()->searchUserCount(array('keywordType' => 'email', 'keyword' => 'not_exist_email@user.com'));
        $this->assertEquals(0, $foundUserCount);
        $foundUserCount = $this->getUserService()->searchUserCount(array('keywordType' => 'loginIp', 'keyword' => '192.168.0.1'));
        $this->assertEquals(0, $foundUserCount);
    }

    public function testSetEmailVerified()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
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
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->changeNickname($registeredUser['id'], 'hello123');
        $result = $this->getUserService()->getUser($registeredUser['id']);
        $this->assertEquals($result['nickname'], 'hello123');
    }

    /**
     * @expectedException \Topxia\Common\Exception\ResourceNotFoundException
     */
    public function testChangeNicknameOne()
    {
        $user = null;
        $this->getUserService()->changeNickname($user['id'], 'hello123');
    }

    /**
     * @expectedException \Topxia\Common\Exception\UnexpectedValueException
     */
    public function testChangeNicknameTwo()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->changeNickname($registeredUser['id'], 'hell_!!o123');
    }

    /**
     * @expectedException \Topxia\Common\Exception\UnexpectedValueException
     */
    public function testChangeNicknameThree()
    {
        $user           = $this->createUser('user');
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->changeNickname($registeredUser['id'], 'user');
    }

    public function testChangeEmail()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->changeEmail($registeredUser['id'], 'change@change.com');
        $foundUser = $this->getUserService()->getUser($registeredUser['id']);
        $this->assertEquals('change@change.com', $foundUser['email']);
    }

    /**
     * @expectedException \Topxia\Common\Exception\UnexpectedValueException
     */
    public function testChangeEmailWithErrorEmailFormat1()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->changeEmail($registeredUser['id'], 'change@ch_ange.com');
    }

    /**
     * @expectedException \Topxia\Common\Exception\UnexpectedValueException
     */
    public function testChangeEmailWithErrorEmailFormat2()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->changeEmail($registeredUser['id'], 'changechange.com');
    }

    /**
     * @expectedException \Topxia\Common\Exception\UnexpectedValueException
     */
    public function testChangeEmailWithExistEmail()
    {
        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');
        $this->getUserService()->changeEmail($user1['id'], 'user2@user2.com');
    }

    public function testChangeAvatar()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );

        $registeredUser = $this->getUserService()->register($userInfo);
        $data           = array(
            'id'   => '1',
            'type' => 'jpg',
        );
        //$a              = $this->getUserService()->changeAvatar($registeredUser['id'], $data);
    }

    public function testIsEmailAvaliable()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
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
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
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
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->assertTrue($this->getUserService()->verifyPassword($registeredUser['id'], $userInfo['password']));

        $this->getUserService()->changePassword($registeredUser['id'], 'new_password');
        $changePasswordedUser = $this->getUserService()->getUser($registeredUser['id']);
        $this->assertTrue($this->getUserService()->verifyPassword($changePasswordedUser['id'], 'new_password'));
    }

    /**
     * @expectedException \Topxia\Common\Exception\InvalidArgumentException
     */
    public function testChangePasswordTwice()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->changePassword($registeredUser['id'], '');
    }

    /**
     * @expectedException \Topxia\Common\Exception\InvalidArgumentException
     */
    public function testChangePayPasswordOne()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->changePayPassword($registeredUser['id'], '');
    }

    public function testChangePayPasswordTwice()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $newPayPassword = '12345asd';
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->changePayPassword($registeredUser['id'], $newPayPassword);
    }

    public function testIsMobileUnique()
    {
        $userInfo       = array(
            'nickname'       => 'test_nickname',
            'password'       => 'test_password',
            'email'          => 'test_email@email.com',
            'verifiedMobile' => '13777868634'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $result         = $this->getUserService()->isMobileUnique('13777868634');
        $this->assertFalse($result);
        $result = $this->getUserService()->isMobileUnique('18777868634');
        $this->assertTrue($result);
    }

    public function testChangeMobileOne()
    {
        $userInfo       = array(
            'nickname'       => 'test_nickname',
            'password'       => 'test_password',
            'email'          => 'test_email@email.com',
            'verifiedMobile' => '13777868634'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $result         = $this->getUserService()->changeMobile($registeredUser['id'], '18257739598');
        $this->assertTrue($result);
    }

    /**
     * @expectedException \Topxia\Common\Exception\InvalidArgumentException
     */
    public function testChangeMobileTwice()
    {
        $userInfo       = array(
            'nickname'       => 'test_nickname',
            'password'       => 'test_password',
            'email'          => 'test_email@email.com',
            'verifiedMobile' => '13777868634'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $result         = $this->getUserService()->changeMobile($registeredUser['id'], '');
    }

    public function testGetUserSecureQuestionsByUserId()
    {
        $userInfo       = array(
            'nickname'       => 'test_nickname',
            'password'       => 'test_password',
            'email'          => 'test_email@email.com',
            'verifiedMobile' => '13777868634'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $fields         = array(
            'securityQuestion1' => 'question-1',
            'securityAnswer1'   => 'answer-1',
            'securityQuestion2' => 'question-2',
            'securityAnswer2'   => 'answer-2',
            'securityQuestion3' => 'question-3',
            'securityAnswer3'   => 'answer-3'
        );
        $this->getUserService()->addUserSecureQuestionsWithUnHashedAnswers($registeredUser['id'], $fields);
        $result = $this->getUserService()->getUserSecureQuestionsByUserId($registeredUser['id']);
        $this->assertEquals(3, count($result));
    }

    public function testAddUserSecureQuestionsWithUnHashedAnswers()
    {
        $userInfo       = array(
            'nickname'       => 'test_nickname',
            'password'       => 'test_password',
            'email'          => 'test_email@email.com',
            'verifiedMobile' => '13777868634'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $fields         = array(
            'securityQuestion1' => 'question-1',
            'securityAnswer1'   => 'answer-1',
            'securityQuestion2' => 'question-2',
            'securityAnswer2'   => 'answer-2',
            'securityQuestion3' => 'question-3',
            'securityAnswer3'   => 'answer-3'
        );
        $this->getUserService()->addUserSecureQuestionsWithUnHashedAnswers($registeredUser['id'], $fields);
        $result = $this->getUserService()->getUserSecureQuestionsByUserId($registeredUser['id']);
        $this->assertEquals(3, count($result));
    }

    public function testVerifyInSaltOut()
    {
        $in     = 'test';
        $out    = 'xw4L6lqFZ9b43YFhZKn73sOgZpK52o/GE60emMO4AUo=';
        $salt   = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $result = $this->getUserService()->verifyInSaltOut($in, $salt, $out);
        $this->assertFalse($result);
    }

    public function testVerifyPasswordOne()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->assertFalse($this->getUserService()->verifyPassword($registeredUser['id'], 'password'));
        $this->assertTrue($this->getUserService()->verifyPassword($registeredUser['id'], 'test_password'));
    }

    /**
     * @expectedException \Topxia\Common\Exception\ResourceNotFoundException
     */
    public function testVerifyPayPasswordTwice()
    {
        $registeredUser = null;
        $this->assertFalse($this->getUserService()->verifyPassword($registeredUser['id'], 'password'));
    }

    public function testParseRegistration()
    {
        $auth["register_mode"] = "email_or_mobile";
        $this->getSettingService()->set('auth', $auth);
        $registration['emailOrMobile'] = '627099747@qq.com';
        $result                        = $this->getUserService()->parseRegistration($registration);
        $this->assertEquals('627099747@qq.com', $result['emailOrMobile']);
    }

    public function testParseRegistrationTwice()
    {
        $auth["register_mode"] = "email_or_mobile";
        $this->getSettingService()->set('auth', $auth);
        $registration['emailOrMobile'] = '13777777976';
        $result                        = $this->getUserService()->parseRegistration($registration);
        $this->assertEquals('13777777976', $result['mobile']);
    }

    /**
     * @expectedException \Topxia\Common\Exception\InvalidArgumentException
     */
    public function testParseRegistrationThird()
    {
        $auth["register_mode"] = "email_or_mobile";
        $this->getSettingService()->set('auth', $auth);
        $registration['emailOrMobile'] = '';
        $this->getUserService()->parseRegistration($registration);
    }

    /**
     * @expectedException \Topxia\Common\Exception\UnexpectedValueException
     */
    public function testParseRegistrationForth()
    {
        $auth["register_mode"] = "email_or_mobile";
        $this->getSettingService()->set('auth', $auth);
        $registration['emailOrMobile'] = 'x';
        $this->getUserService()->parseRegistration($registration);
    }

    public function testParseRegistrationFifth()
    {
        $auth["register_mode"] = "mobile";
        $this->getSettingService()->set('auth', $auth);
        $registration['mobile'] = '13777822976';
        $result                 = $this->getUserService()->parseRegistration($registration);
        $this->assertEquals('13777822976', $result['mobile']);
    }

    /**
     * @expectedException \Topxia\Common\Exception\UnexpectedValueException
     */
    public function testParseRegistrationSixth()
    {
        $auth["register_mode"] = "mobile";
        $this->getSettingService()->set('auth', $auth);
        $registration['mobile'] = 'z';
        $this->getUserService()->parseRegistration($registration);
    }

    /**
     * @expectedException \Topxia\Common\Exception\UnexpectedValueException
     */
    public function testParseRegistrationSeventh()
    {
        $auth["register_mode"] = "mobile";
        $this->getSettingService()->set('auth', $auth);
        $registration['mobile'] = 'x';
        $this->getUserService()->parseRegistration($registration);
    }

    public function testParseRegistrationEighth()
    {
        $auth["register_mode"] = "";
        $this->getSettingService()->set('auth', $auth);
        $registration = null;
        $this->getUserService()->parseRegistration($registration);
    }

    public function testIsMobileRegisterMode()
    {
        $auth["register_mode"] = "mobile";
        $this->getSettingService()->set('auth', $auth);
        $result = $this->getUserService()->IsMobileRegisterMode();
        $this->assertTrue($result);
    }

    public function testGenerateNickname()
    {
        $userInfo = array(
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $nickname = $this->getUserService()->generateNickname($userInfo);
        $this->assertNotNull($nickname);
    }

    public function testGenerateEmail()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password'
        );
        $email    = $this->getUserService()->generateEmail($userInfo);
        $this->assertNotNull($email);
    }

// public function testImportUpdateEmail()

// {

//     // $user1 = $this->createUser('user1');

//     // $user2 = $this->createUser('user2');

//     // $user3 = $this->createUser('user3');

//     // $users = array($user1,$user2,$user3);

//     // $this->getUserService()->importUpdateEmail($users);
    // }

    public function testSetupAccount()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com',
            'token'    => array('userId' => 999, 'token' => 'token', 'expiredTime' => strtotime('+1 day'))
        );
        $registeredUser = $this->getUserService()->register($userInfo, 'weibo');
        $this->assertEquals('0', $registeredUser['setup']);
        $result = $this->getUserService()->setupAccount($registeredUser['id']);
        $this->assertEquals('1', $result['setup']);
    }

    /**
     * @expectedException \Topxia\Common\Exception\ResourceNotFoundException
     */
    public function testSetupAccountTwice()
    {
        $user   = null;
        $result = $this->getUserService()->setupAccount($user['id']);
    }

    /**
     * @expectedException \Topxia\Common\Exception\RuntimeException
     */
    public function testSetupAccountThird()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $result         = $this->getUserService()->setupAccount($registeredUser['id']);
    }

    /**
     * @expectedException \Topxia\Common\Exception\InvalidArgumentException
     */
    public function testChangePasswordWithEmptyPassword()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->changePassword($registeredUser['id'], '');
    }

    /**
     * @expectedException \Topxia\Common\Exception\ResourceNotFoundException
     */
    public function testChangePasswordWithNotExistUserId()
    {
        $this->getUserService()->changePassword(0, 'new_password');
    }

    /**
     * @expectedException \Topxia\Common\Exception\ResourceNotFoundException
     */
    public function testVerifyPasswordTwice()
    {
        $this->getUserService()->verifyPassword(0, 'password');
    }

    /**
     * @expectedException \Topxia\Common\Exception\ResourceNotFoundException
     */
    public function testVerifyPasswordWithNotExistUser()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->verifyPassword(0, 'password');
    }

    /**
     *  error
     */
    public function testFilterFollowingIds()
    {
        $fromUser     = $this->createFromUser();
        $toUser       = $this->createToUser();
        $followed     = $this->getUserService()->follow($fromUser['id'], $toUser['id']);
        $followingIds = $this->getUserService()->filterFollowingIds($fromUser['id'], array(999, $toUser['id'], 777));
        $this->assertContains($toUser['id'], $followingIds);
    }

    public function testFollowOnce() //touser 是被关注者

    {
        $fromUser = $this->createFromUser();
        $toUser   = $this->createToUser();
        $followed = $this->getUserService()->follow($fromUser['id'], $toUser['id']);
        $this->assertEquals($fromUser['id'], $followed['fromId']);
        $this->assertEquals($toUser['id'], $followed['toId']);
    }

    public function testFindUserFollowing()
    {
        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');
        $this->getUserService()->follow($user1['id'], $user2['id']);
        $result = $this->getUserService()->findUserFollowing($user1['id'], 0, 20);
        $this->assertEquals(1, count($result));
    }

    public function testFindAllUserFollowing()
    {
        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');
        $user3 = $this->createUser('user3');
        $this->getUserService()->follow($user1['id'], $user3['id']);
        $this->getUserService()->follow($user1['id'], $user2['id']);
        $result = $this->getUserService()->findAllUserFollowing($user1['id'], 0, 20);
        $this->assertEquals(2, count($result));
    }

    public function testFindUserFollowingCount()
    {
        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');
        $user3 = $this->createUser('user3');
        $this->getUserService()->follow($user1['id'], $user3['id']);
        $this->getUserService()->follow($user1['id'], $user2['id']);
        $result = $this->getUserService()->findAllUserFollowing($user1['id'], 0, 20);
        $this->assertEquals(2, count($result));
    }

    public function testFindUserFollowers()
    {
        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');
        $user3 = $this->createUser('user3');
        $this->getUserService()->follow($user1['id'], $user3['id']);
        $this->getUserService()->follow($user2['id'], $user3['id']);
        $result = $this->getUserService()->findUserFollowers($user3['id'], 0, 20);
        $this->assertEquals(2, count($result));
    }

    public function testFindAllUserFollower()
    {
        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');
        $user3 = $this->createUser('user3');
        $this->getUserService()->follow($user1['id'], $user3['id']);
        $this->getUserService()->follow($user2['id'], $user3['id']);
        $result = $this->getUserService()->findAllUserFollower($user3['id'], 0, 20);
        $this->assertEquals(2, count($result));
    }

    public function testFindUserFollowerCount()
    {
        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');
        $user3 = $this->createUser('user3');
        $this->getUserService()->follow($user1['id'], $user3['id']);
        $this->getUserService()->follow($user3['id'], $user1['id']);
        $result = $this->getUserService()->findUserFollowerCount($user1['id'], 0, 20);
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
     * @expectedException \Topxia\Common\Exception\ResourceNotFoundException
     */
    public function testFollowTwice()
    {
        $user1 = $this->createUser('user1');
        $user2 = null;
        $this->getUserService()->follow($user1['id'], $user2['id']);
    }

    /**
     * @expectedException \Topxia\Common\Exception\InvalidArgumentException
     */
    public function testFollowThird()
    {
        $user1 = $this->createUser('user1');
        $this->getUserService()->follow($user1['id'], $user1['id']);
    }

    /**
     * @expectedException \Topxia\Common\Exception\RuntimeException
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
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $result         = $this->getUserService()->HasAdminRoles($registeredUser['id']);
        $this->assertFalse($result);
        $this->getUserService()->changeUserRoles($registeredUser['id'], array(
            'ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER'
        ));
        $result = $this->getUserService()->HasAdminRoles($registeredUser['id']);
        $this->assertTrue($result);
    }

    /**
     *  follow
     */
    public function testUnFollow()
    {
        $fromUser = $this->createFromUser();
        $toUser   = $this->createToUser();
        $this->getUserService()->follow($fromUser['id'], $toUser['id']);
        $result = $this->getUserService()->unFollow($fromUser['id'], $toUser['id']);
        $this->assertEquals(1, $result);
    }

    /**
     * @expectedException \Topxia\Common\Exception\ResourceNotFoundException
     */
    public function testUnFollowTwcie()
    {
        $user1 = $this->createUser('user1');
        $user2 = null;
        $this->getUserService()->unFollow($user1['id'], $user2['id']);
    }

    /**
     * @expectedException \Topxia\Common\Exception\RuntimeException
     */
    public function testUnFollowThird()
    {
        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');
        $this->getUserService()->unFollow($user1['id'], $user2['id']);
    }

    /**
     *  follow
     * @expectedException \Topxia\Common\Exception\ResourceNotFoundException
     */
    public function testUnFollowNotExistUser()
    {
        $fromUser = $this->createFromUser();
        $toUser   = $this->createToUser();
        $this->getUserService()->unFollow($fromUser['id'], 0);
    }

    /**
     *  follow
     * @expectedException \Topxia\Common\Exception\RuntimeException
     */
    public function testUnFollowWithoutFollowed()
    {
        $fromUser = $this->createFromUser();
        $toUser   = $this->createToUser();
        $this->getUserService()->unFollow($fromUser['id'], $toUser['id']);
    }

    /**
     *   follow
     */
    public function testIsFollowed()
    {
        $fromUser = $this->createFromUser();
        $toUser   = $this->createToUser();
        $this->assertFalse($this->getUserService()->isFollowed($fromUser['id'], $toUser['id']));

        $this->getUserService()->follow($fromUser['id'], $toUser['id']);
        $this->assertTrue($this->getUserService()->isFollowed($fromUser['id'], $toUser['id']));
    }

    /**
     *  follow
     * @expectedException \Topxia\Common\Exception\ResourceNotFoundException
     */
    public function testIsFollowedTwice()
    {
        $user1  = null;
        $toUser = $this->createToUser();
        $this->getUserService()->isFollowed($user1['id'], $toUser['id']);
    }

    /**
     *  follow
     * @expectedException \Topxia\Common\Exception\ResourceNotFoundException
     */
    public function testIsFollowedThird()
    {
        $fromUser = $this->createFromUser();
        $user2    = null;
        $this->getUserService()->isFollowed($fromUser['id'], $user2['id']);
    }

    public function testGetLastestApprovalByUserIdAndStatus()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $result         = $this->getUserService()->getLastestApprovalByUserIdAndStatus($registeredUser['id'], 'approving');
        $this->assertFalse($result);
    }

    public function testfindUserApprovalsByUserIds()
    {
        $users  = array();
        $result = $this->getUserService()->findUserApprovalsByUserIds($users);
        $this->assertEquals(0, count($result));
    }

// public function testApplyUserApproval()//*

// {

// }
    /**
     * @expectedException \Topxia\Common\Exception\ResourceNotFoundException
     */
    public function testApplyUserApprovalTwice()
    {
        $userId    = null;
        $approval  = null;
        $faceImg   = null;
        $backImg   = null;
        $directory = null;
        $this->getUserService()->applyUserApproval($userId, $approval, $faceImg, $backImg, $directory);
    }

    public function testPassApproval()
    {
    }

    /**
     * @expectedException \Topxia\Common\Exception\ResourceNotFoundException
     */
    public function testPassApprovalTwice()
    {
        $user = null;
        $note = null;
        $this->getUserService()->passApproval($user['id'], $note);
    }

    public function testRejectApproval()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $result         = $this->getUserService()->rejectApproval($registeredUser['id']);
        $this->assertTrue($result);
    }

    /**
     * @expectedException \Topxia\Common\Exception\ResourceNotFoundException
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
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $sessionId      = '123.0.0.1';
        $this->getUserService()->rememberLoginSessionId($registeredUser['id'], $sessionId);
        $result = $this->getUserService()->getUser($registeredUser['id']);
        $this->assertNotNull($result['loginSessionId']);
    }

    /**
     * @expectedException \Topxia\Common\Exception\ResourceNotFoundException
     */
    public function testRememberLoginSessionIdTwice()
    {
        $user      = null;
        $sessionId = '123.0.0.1';
        $this->getUserService()->rememberLoginSessionId($user['id'], $sessionId);
    }

    public function testAnalysisRegisterDataByTime()
    {
        $time1  = time();
        $user1  = $this->createUser('user1');
        $user2  = $this->createUser('user2');
        $user3  = $this->createUser('user3');
        $time2  = time();
        $arrays = $this->getUserService()->analysisRegisterDataByTime($time1, $time2);
        $result = $arrays['0'];
        $this->assertGreaterThanOrEqual('3', $result['count']);
    }

    public function testAnalysisUserSumByTime()
    {
        $user1  = $this->createUser('user1');
        $user2  = $this->createUser('user2');
        $user3  = $this->createUser('user3');
        $time2  = time();
        $arrays = $this->getUserService()->analysisUserSumByTime($time2);
        $result = $arrays['0'];
        $this->assertEquals('4', $result['count']);
    }

    public function testParseAts()
    {
        $user1  = $this->createUser('user1');
        $user2  = $this->createUser('user2');
        $user3  = $this->createUser('user3');
        $text   = '看我召唤三只猪!@user1,@user2,@user3,谢谢!';
        $result = $this->getUserService()->parseAts($text);
        $this->assertEquals(3, count($result));
    }

    /**
     *   follow
     * @expectedException \Topxia\Common\Exception\ResourceNotFoundException
     */
    public function testIsFollowWithNotExistToId()
    {
        $fromUser = $this->createFromUser();
        $this->getUserService()->isFollowed($fromUser['id'], 888);
    }

    /**
     *   follow
     * @expectedException \Topxia\Common\Exception\ResourceNotFoundException
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
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $userProfile    = $this->getUserService()->getUserProfile($registeredUser['id']);

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
            'truename'  => 'truename',
            'gender'    => 'male',
            'birthday'  => '2013-01-01',
            'city'      => '10000',
            'mobile'    => '13888888888',
            'qq'        => '123456',
            'company'   => 'company',
            'job'       => 'job',
            'signature' => 'signature',
            'about'     => 'about'
        );

        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
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
     * @expectedException \Topxia\Common\Exception\ResourceNotFoundException
     */
    public function testUpdateUserProfileWithNotExistUser()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->updateUserProfile(999, array('gender' => 'male'));
    }

    /**
     *  profile
     * @expectedException \Topxia\Common\Exception\UnexpectedValueException
     */
    public function testUpdateUserProfileWithErrorGender()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->updateUserProfile($registeredUser['id'], array('gender' => 'xxx'));
    }

    /**
     *  profile
     * @expectedException \Topxia\Common\Exception\UnexpectedValueException
     */
    public function testUpdateUserProfileWithErrorBirthday()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->updateUserProfile($registeredUser['id'], array('birthday' => 'xxx'));
    }

    /**
     *  profile
     * @expectedException \Topxia\Common\Exception\UnexpectedValueException
     */
    public function testUpdateUserProfileWithErrorMobile()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->updateUserProfile($registeredUser['id'], array('mobile' => '8888'));
    }

    /**
     *  profile
     * @expectedException \Topxia\Common\Exception\UnexpectedValueException
     */
    public function testUpdateUserProfileWithErrorQQ()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->updateUserProfile($registeredUser['id'], array('qq' => '1'));
    }

    /**
     *  roles
     *
     */
    public function testChangeUserRoles()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);

        $this->getUserService()->changeUserRoles($registeredUser['id'], array(
                'ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER'
            )
        );
        $foundUser = $this->getUserService()->getUser($registeredUser['id']);
        $this->assertEquals(array('ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER'), $foundUser['roles']);

        $this->getUserService()->changeUserRoles($registeredUser['id'], array(
                'ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN'
            )
        );
        $foundUser = $this->getUserService()->getUser($registeredUser['id']);
        $this->assertEquals(array('ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN'), $foundUser['roles']);
    }

    /**
     *  roles
     * @expectedException \Topxia\Common\Exception\InvalidArgumentException
     */
    public function testChangeUserRolesWithEmptyRoles()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->changeUserRoles($registeredUser['id'], array());
    }

    /**
     *  roles
     * @expectedException \Topxia\Common\Exception\ResourceNotFoundException
     *
     */
    public function testChangeUserRolesWithNotExistUser()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->changeUserRoles(999, array('ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER'));
    }

    /**
     *  roles
     * @expectedException \Topxia\Common\Exception\UnexpectedValueException
     *
     */
    public function testChangeUserRolesWithIllegalRoles()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->changeUserRoles($registeredUser['id'], array('ROLE_NOTEXIST_USER'));
    }

    /**
     *  token
     */
    public function testMakeToken()
    {
        $userInfo          = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser    = $this->getUserService()->register($userInfo);
        $passwordRestToken = $this->getUserService()->makeToken('password-reset', $registeredUser['id'], 1371801141, 'password-reset-data');
        $emailVerifyToken  = $this->getUserService()->makeToken('email-verify', $registeredUser['id'], 1371801141, 'data');
        $this->assertNotNull($passwordRestToken);
        $this->assertNotNull($emailVerifyToken);
    }

    public function testGetToken()
    {
        $userInfo                = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser          = $this->getUserService()->register($userInfo);
        $passwordRestToken       = $this->getUserService()->makeToken('password-reset', $registeredUser['id'], strtotime('+1 day'), 'password-reset-data');
        $foundPasswordResetToken = $this->getUserService()->getToken('password-reset', $passwordRestToken);
        $this->assertEquals($registeredUser['id'], $foundPasswordResetToken['userId']);
        $this->assertEquals('password-reset', $foundPasswordResetToken['type']);
        $this->assertEquals('password-reset-data', $foundPasswordResetToken['data']);
    }

    /**
     *  token
     */
    public function testGetTokenSuccess()
    {
        $userInfo                = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser          = $this->getUserService()->register($userInfo);
        $passwordRestToken       = $this->getUserService()->makeToken('password-reset', $registeredUser['id'], strtotime('+1 day'), 'password-reset-data');
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
        $userInfo          = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser    = $this->getUserService()->register($userInfo);
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
        $userInfo          = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser    = $this->getUserService()->register($userInfo);
        $passwordRestToken = $this->getUserService()->makeToken('password-reset', $registeredUser['id'], 1000, 'password-reset-data');

        $foundPasswordResetToken = $this->getUserService()->getToken('password-reset', $passwordRestToken);
        $this->assertNull($foundPasswordResetToken);
    }

    public function testSearchTokenCount()
    {
        $userInfo         = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser   = $this->getUserService()->register($userInfo);
        $emailVerifyToken = $this->getUserService()->makeToken('email-verify', $registeredUser['id'], 1371801141, 'data');
        $result           = $this->getUserService()->searchTokenCount(array('type' => 'email-verify'));
        $this->assertEquals('1', $result);
    }

    /**
     *  token
     */
    public function testDeleteToken()
    {
        $userInfo          = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser    = $this->getUserService()->register($userInfo);
        $passwordRestToken = $this->getUserService()->makeToken('password-reset', $registeredUser['id'], 1000, 'password-reset-data');
        $deleteResult      = $this->getUserService()->deleteToken('password-reset', $passwordRestToken);
        $this->assertTrue($deleteResult);
    }

    /**
     *  token
     */
    public function testDeleteTokenFailed()
    {
        $userInfo          = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser    = $this->getUserService()->register($userInfo);
        $passwordRestToken = $this->getUserService()->makeToken('password-reset', $registeredUser['id'], 1000, 'password-reset-data');
        $deleteResult      = $this->getUserService()->deleteToken('error_type', $passwordRestToken);
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
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->assertEquals(0, $registeredUser['locked']);
        $this->getUserService()->lockUser($registeredUser['id']);
        $registeredUser = $this->getUserService()->getUser($registeredUser['id']);
        $this->assertEquals(1, $registeredUser['locked']);
    }

    /**
     *   lock
     * @expectedException \Topxia\Common\Exception\ResourceNotFoundException
     */
    public function testLockUserTwice()
    {
        $user = null;
        $this->getUserService()->lockUser($user['id']);
    }

    /**
     *  lock
     * @expectedException \Topxia\Common\Exception\ResourceNotFoundException
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
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->assertEquals(0, $registeredUser['locked']);
        $this->getUserService()->lockUser($registeredUser['id']);
        $this->getUserService()->unlockUser($registeredUser['id']);
        $registeredUser = $this->getUserService()->getUser($registeredUser['id']);
        $this->assertEquals(0, $registeredUser['locked']);
    }

    /**
     * @expectedException \Topxia\Common\Exception\ResourceNotFoundException
     */
    public function testUnLockUserTwice()
    {
        $user = null;
        $this->getUserService()->unlockUser($user);
    }

    public function testPromoteUser()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->promoteUser($registeredUser['id'], 1);
        $registeredUser = $this->getUserService()->getUser($registeredUser['id']);
        $this->assertEquals(1, $registeredUser['promoted']);
        $this->assertGreaterThan(0, $registeredUser['promotedTime']);
    }

    /**
     * @expectedException \Topxia\Common\Exception\ResourceNotFoundException
     */
    public function testPromoteUserTwice()
    {
        $user = null;
        $this->getUserService()->promoteUser($user, 1);
    }

    public function testCancelPromoteUser()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
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
     * @expectedException \Topxia\Common\Exception\ResourceNotFoundException
     */
    public function testCancelPromoteUserTwice()
    {
        $user = null;
        $this->getUserService()->cancelPromoteUser($user);
    }

    public function testFindLatestPromotedTeacher()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->changeUserRoles($registeredUser['id'], array(
            'ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER'
        ));
        $this->getUserService()->promoteUser($registeredUser['id'], 1);
        $result = $this->getUserService()->findLatestPromotedTeacher(0, 20);
        $result = $result['0'];
        $this->assertEquals($registeredUser['id'], $result['id']);
    }

    public function testWaveUserCounter()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->waveUserCounter($registeredUser['id'], 'newNotificationNum', 1);
        $foundUser = $this->getUserService()->getUser($registeredUser['id']);
        $this->assertEquals('1', $foundUser['newNotificationNum']);
    }

    /**
     * @expectedException \Topxia\Common\Exception\UnexpectedValueException
     */
    public function testWaveUserCounterTwice()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->waveUserCounter($registeredUser['id'], 'newMessageNum', 'ss');
    }

    public function testClearUserCounter()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
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
     *  lock
     * @expectedException \Topxia\Common\Exception\ResourceNotFoundException
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
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->bindUser('qq', 123123123, $registeredUser['id'], array('token' => 'token', 'expiredTime' => strtotime('+1 day')));
        $user = $this->getUserService()->getUserBindByToken('token');
        $this->assertEquals($registeredUser['id'], $user['toId']);
    }

    public function testMarkLoginInfo()
    {
        $this->getUserService()->markLoginInfo();
    }

    public function testMarkLoginFailed()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $ip             = '127.0.0.1';
        $result         = $this->getUserService()->markLoginFailed($registeredUser['id'], $ip);
        $this->assertNotNull($result);
    }

    public function testMarkLoginSuccess()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $ip             = '152.0.1';
        $result         = $this->getUserService()->markLoginSuccess($registeredUser['id'], $ip);
        $this->assertNull($result);
    }

    public function testCheckLoginForbidden()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $ip             = '152.0.1';
        $result         = $this->getUserService()->checkLoginForbidden($registeredUser['id'], $ip);
        $this->assertEquals('ok', $result['status']);
    }

    /**
     *  bind
     * @expectedException \Topxia\Common\Exception\ResourceNotFoundException
     */
    public function testBindNotExistUser()
    {
        $this->getUserService()->bindUser('qq', 123123123, 999, array('token' => 'token', 'expiredTime' => strtotime('+1 day')));
    }

    /**
     *  bind
     * @expectedException \Topxia\Common\Exception\UnexpectedValueException
     */
    public function testBindUserWithTypeNotInWeiboQQRenren()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $foundBind      = $this->getUserService()->bindUser('douban', 123123123, $registeredUser['id'], array('token' => 'token', 'expiredTime' => strtotime('+1 day')));
    }

    /**
     *  bind
     */
    public function testGetUserBind()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
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
     *  bind
     */
    public function testGetUserBindWithErrorType()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->bindUser('qq', 123123123, $registeredUser['id'], array(
            'token' => 'token', 'expiredTime' => strtotime('+1 day')
        ));
        $this->getUserService()->getUserBindByTypeAndFromId('douban', 123123123);
    }

    /**
     *  bind
     */
    public function testGetUserBindWithErrorParamaters()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->bindUser('qq', 123123123, $registeredUser['id'], array('token' => 'token', 'expiredTime' => strtotime('+1 day')));
        $this->getUserService()->getUserBindByTypeAndFromId('qq', 7777);
        $this->getUserService()->getUserBindByTypeAndFromId('douban', 123123123);
    }

    /**
     *  bind
     */
    public function testGetUserBindWithExpiredTimeInvalidate()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->bindUser('qq', 123123123, $registeredUser['id'], array('token' => 'token', 'expiredTime' => 100));
        $this->getUserService()->getUserBindByTypeAndFromId('qq', 123123123);
    }

    /**
     *  bind
     */
    public function testGetUserBindByTypeAndUserId()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
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
     *  bind
     * @expectedException \Topxia\Common\Exception\ResourceNotFoundException
     */
    public function testGetUserBindByTypeAndUserIdTwice()
    {
        $registeredUser = null;
        $foundBind      = $this->getUserService()->getUserBindByTypeAndUserId('qq', $registeredUser['id']);
    }

    /**
     *  bind
     * @expectedException \Topxia\Common\Exception\UnexpectedValueException
     */
    public function testGetUserBindByTypeAndUserIdThird()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $type           = null;
        $foundBind      = $this->getUserService()->getUserBindByTypeAndUserId($type, $registeredUser['id']);
    }

    /**
     *  bind
     * @expectedException \Topxia\Common\Exception\ResourceNotFoundException
     */
    public function testGetUserBindWithInvalidateUserId()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->bindUser('qq', 123123123, $registeredUser['id'], array('token' => 'token', 'expiredTime' => 100));
        $this->getUserService()->getUserBindByTypeAndUserId('qq', 999);
    }

    /**
     *  bind
     * @expectedException \Topxia\Common\Exception\UnexpectedValueException
     */
    public function testGetUserBindByTypeAndUserIdWithTypeNotInWeiboQQRenren()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->bindUser('qq', 123123123, $registeredUser['id'], array('token' => 'token', 'expiredTime' => 100));
        $this->getUserService()->getUserBindByTypeAndUserId('douban', $registeredUser['id']);
    }

    /**
     *  bind
     */
    public function testFindBindsByUserIdOne()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->bindUser('qq', 111111, $registeredUser['id'], array('token' => 'token', 'expiredTime' => 100));
        $this->getUserService()->bindUser('renren', 222222, $registeredUser['id'], array('token' => 'token', 'expiredTime' => 100));
        $this->getUserService()->bindUser('weibo', 333333, $registeredUser['id'], array('token' => 'token', 'expiredTime' => 100));
        $userBinds = $this->getUserService()->findBindsByUserId($registeredUser['id']);
        $fromIds   = array();

        foreach ($userBinds as $userBind) {
            array_push($fromIds, $userBind['fromId']);
        }

        $this->assertContains(111111, $fromIds);
        $this->assertContains(222222, $fromIds);
        $this->assertContains(333333, $fromIds);
    }

    /**
     *  bind
     * @expectedException \Topxia\Common\Exception\ResourceNotFoundException
     */
    public function testFindBindsByUserIdTwice()
    {
        $user = null;
        $this->getUserService()->findBindsByUserId($user['id']);
    }

    /**
     *  bind
     * @expectedException \Topxia\Common\Exception\ResourceNotFoundException
     */
    public function testFindBindsByErrorUserId()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->bindUser('qq', 111111, $registeredUser['id'], array('token' => 'token', 'expiredTime' => 100));
        $this->getUserService()->bindUser('renren', 222222, $registeredUser['id'], array('token' => 'token', 'expiredTime' => 100));
        $this->getUserService()->bindUser('weibo', 333333, $registeredUser['id'], array('token' => 'token', 'expiredTime' => 100));
        $this->getUserService()->findBindsByUserId(999);
    }

    /**
     *  bind
     */
    public function testUnBindUserByTypeAndToIdOne()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->bindUser('qq', 111111, $registeredUser['id'], array('token' => 'token', 'expiredTime' => 100));

        $result = $this->getUserService()->getUserBindByTypeAndUserId('qq', $registeredUser['id']);
        $this->assertNotNull($result);
        $this->getUserService()->unBindUserByTypeAndToId('qq', $registeredUser['id']);
        $result = $this->getUserService()->getUserBindByTypeAndUserId('qq', $registeredUser['id']);
        $this->assertFalse($result);
    }

    /**
     *  bind
     * @expectedException \Topxia\Common\Exception\ResourceNotFoundException
     */
    public function testUnBindUserByTypeAndToIdTwice()
    {
        $type = null;
        $user = null;
        $this->getUserService()->unBindUserByTypeAndToId($type, $user['id']);
    }

    /**
     *  bind
     * @expectedException \Topxia\Common\Exception\UnexpectedValueException
     */
    public function testUnBindUserByTypeAndToIdThird()
    {
        $type           = null;
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->unBindUserByTypeAndToId($type, $registeredUser['id']);
    }

    public function testGetUserBindByTypeAndFromId()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
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
        $bank  = $this->getUserService()->createUserPayAgreement($field);
        $this->assertEquals('农业银行', $bank['bankName']);
    }

    public function testGetUserPayAgreement()
    {
        $field    = array('userId' => 1, 'type' => 0, 'bankName' => '农业银行', 'bankNumber' => 1124, 'bankAuth' => '0eeeee', 'bankId' => 1);
        $bank     = $this->getUserService()->createUserPayAgreement($field);
        $authBank = $this->getUserService()->getUserPayAgreement($bank['id']);
        $this->assertEquals('农业银行', $authBank['bankName']);
    }

    public function testGetUserPayAgreementByUserIdAndBankAuth()
    {
        $field    = array('userId' => 1, 'type' => 0, 'bankName' => '农业银行', 'bankNumber' => 1124, 'bankAuth' => '0eeeee', 'bankId' => 1);
        $bank     = $this->getUserService()->createUserPayAgreement($field);
        $authBank = $this->getUserService()->getUserPayAgreementByUserIdAndBankAuth(1, '0eeeee');
        $this->assertEquals('农业银行', $authBank['bankName']);
    }

    public function testGetUserPayAgreementByUserId()
    {
        $field    = array('userId' => 1, 'type' => 0, 'bankName' => '农业银行', 'bankNumber' => 1124, 'bankAuth' => '0eeeee', 'bankId' => 1);
        $bank     = $this->getUserService()->createUserPayAgreement($field);
        $authBank = $this->getUserService()->getUserPayAgreementByUserId(1);
        $this->assertEquals('农业银行', $authBank['bankName']);
    }

    public function testUpdateUserPayAgreementByUserIdAndBankAuth()
    {
        $field    = array('userId' => 1, 'type' => 0, 'bankName' => '农业银行', 'bankNumber' => 1124, 'bankAuth' => '0eeeee', 'bankId' => 1);
        $bank     = $this->getUserService()->createUserPayAgreement($field);
        $authBank = $this->getUserService()->updateUserPayAgreementByUserIdAndBankAuth(1, '0eeeee', array('bankName' => '招商银行'));
        $this->assertEquals(1, 1);
    }

    public function testFindUserPayAgreementsByUserId()
    {
        $field    = array('userId' => 1, 'type' => 0, 'bankName' => '农业银行', 'bankNumber' => 1124, 'bankAuth' => '0eeeee', 'bankId' => 1);
        $bank     = $this->getUserService()->createUserPayAgreement($field);
        $authBank = $this->getUserService()->findUserPayAgreementsByUserId(1);
        $this->assertEquals('农业银行', $authBank[0]['bankName']);
    }

    public function testDeleteUserPayAgreements()
    {
        $field             = array('userId' => 1, 'type' => 0, 'bankName' => '农业银行', 'bankNumber' => 1124, 'bankAuth' => '0eeeee', 'bankId' => 1);
        $bank              = $this->getUserService()->createUserPayAgreement($field);
        $userPayAgreements = $this->getUserService()->deleteUserPayAgreements(1);
        $this->assertEquals(1, $userPayAgreements);
    }

    /**
     *  bind
     * @expectedException \Topxia\Common\Exception\ResourceNotFoundException
     */
    public function testUnBindUserByTypeAndToIdWithErrorUserId()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->bindUser('qq', 111111, $registeredUser['id'], array('token' => 'token', 'expiredTime' => 100));
        $this->getUserService()->unBindUserByTypeAndToId('qq', 999);
    }

    /**
     *  bind
     * @expectedException \Topxia\Common\Exception\UnexpectedValueException
     */
    public function testUnBindUserByTypeAndToIdWithErrorType()
    {
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $this->getUserService()->bindUser('qq', 111111, $registeredUser['id'], array('token' => 'token', 'expiredTime' => 100));
        $this->getUserService()->unBindUserByTypeAndToId('douban', $registeredUser['id']);
    }

    /**
     * @group avatar
     */
    public function testChangeAvatarFromImgUrl()
    {
        $this->initFile();
        $userInfo       = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);

        $imgUrl = 'http://wx.qlogo.cn/mmopen/g3MonUZtNHkdmzicIlibx6iaFqAc56vxLSUfpb6n5WKSYVY0ChQKkiaJSgQ1dZuTOgvLLrhJbERQQ4eMsv84eavHiaiceqxibJxCfHe/0';

        //$this->getUserService()->changeAvatarFromImgUrl($registeredUser['id'], $imgUrl);
    }

    protected function createUser($user)
    {
        $userInfo             = array();
        $userInfo['email']    = "{$user}@{$user}.com";
        $userInfo['nickname'] = "{$user}";
        $userInfo['password'] = "{$user}";
        $userInfo['loginIp']  = '127.0.0.1';
        return $this->getUserService()->register($userInfo);
    }

    protected function createFromUser()
    {
        $fromUser             = array();
        $fromUser['email']    = 'fromUser@fromUser.com';
        $fromUser['nickname'] = 'fromUser';
        $fromUser['password'] = 'fromUser';
        return $this->getUserService()->register($fromUser);
    }

    protected function createToUser()
    {
        $toUser             = array();
        $toUser['email']    = 'toUser@toUser.com';
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
            'name'   => '默认文件组',
            'code'   => 'default',
            'public' => 1
        ));

        $this->getFileService()->addFileGroup(array(
            'name'   => '缩略图',
            'code'   => 'thumb',
            'public' => 1
        ));

        $this->getFileService()->addFileGroup(array(
            'name'   => '课程',
            'code'   => 'course',
            'public' => 1
        ));

        $this->getFileService()->addFileGroup(array(
            'name'   => '用户',
            'code'   => 'user',
            'public' => 1
        ));

        $this->getFileService()->addFileGroup(array(
            'name'   => '课程私有文件',
            'code'   => 'course_private',
            'public' => 0
        ));

        $this->getFileService()->addFileGroup(array(
            'name'   => '资讯',
            'code'   => 'article',
            'public' => 1
        ));

        $this->getFileService()->addFileGroup(array(
            'name'   => '临时目录',
            'code'   => 'tmp',
            'public' => 1
        ));

        $this->getFileService()->addFileGroup(array(
            'name'   => '全局设置文件',
            'code'   => 'system',
            'public' => 1
        ));

        $this->getFileService()->addFileGroup(array(
            'name'   => '小组',
            'code'   => 'group',
            'public' => 1
        ));

        $this->getFileService()->addFileGroup(array(
            'name'   => '编辑区',
            'code'   => 'block',
            'public' => 1
        ));

        $this->getFileService()->addFileGroup(array(
            'name'   => '班级',
            'code'   => 'classroom',
            'public' => 1
        ));
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getFileService()
    {
        return $this->getServiceKernel()->createService('Content.FileService');
    }
}
