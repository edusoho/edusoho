<?php

namespace Tests\Unit\User\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class UserDaoTest extends BaseDaoTestCase
{
    public function testGetByEmail()
    {
        $defaultUser = $this->getDefaultMockFields();
        $this->getUserDao()->create($defaultUser);
        $user = $this->getUserDao()->getByEmail($defaultUser['email']);

        $this->assertNotNull($user);
        $this->assertEquals($defaultUser['id'], $user['id']);
    }

    public function testGetUserByType()
    {
        $defaultUser = $this->getDefaultMockFields();
        $this->getUserDao()->create($defaultUser);
        $user = $this->getUserDao()->getUserByType($defaultUser['type']);

        $this->assertNotNull($user);
        $this->assertEquals($defaultUser['id'], $user['id']);
    }

    public function testGetByNickname()
    {
        $defaultUser = $this->getDefaultMockFields();
        $this->getUserDao()->create($defaultUser);
        $user = $this->getUserDao()->getByNickname($defaultUser['nickname']);

        $this->assertNotNull($user);
        $this->assertEquals($defaultUser['id'], $user['id']);
    }

    public function testCountByMobileNotEmpty()
    {
        $defaultUser = $this->getDefaultMockFields();
        $defaultUser['type'] = 'default';
        $this->getUserDao()->create($defaultUser);
        $this->assertEquals(0, $this->getUserDao()->countByMobileNotEmpty());
        $this->getUserProfileDao()->create(array(
            'id' => '3',
            'mobile' => '13967340627',
        ));

        $this->assertEquals(1, $this->getUserDao()->countByMobileNotEmpty());
    }

    public function testFindUnlockedUsersWithMobile()
    {
        $defaultUser = $this->getDefaultMockFields();
        $defaultUser['type'] = 'default';
        $this->getUserDao()->create($defaultUser);
        $this->assertEmpty($this->getUserDao()->findUnlockedUsersWithMobile(0, 10, false));
        $this->assertEmpty($this->getUserDao()->findUnlockedUsersWithMobile(0, 10, true));
        $this->getUserProfileDao()->create(array(
            'id' => '3',
            'mobile' => '13967340627',
        ));
        $this->assertNotEmpty($this->getUserDao()->findUnlockedUsersWithMobile(0, 10, false));
        $this->assertEmpty($this->getUserDao()->findUnlockedUsersWithMobile(0, 10, true));
        $this->getUserDao()->create(array(
            'id' => '5',
            'nickname' => 'test2',
            'roles' => array('ROLE_ADMIN'),
            'password' => '3DMYb8GyEXk32ruFzw4lxy2elz6/aoPtA5X8vCTWezg=',
            'salt' => 'qunt972ow5c48k4wc8k0ss448os0oko',
            'email' => '800@qq.com',
            'type' => 'default',
            'verifiedMobile' => '13967340628',
        ));
        $this->getUserProfileDao()->create(array(
            'id' => '5',
            'mobile' => '13967340628',
        ));
        $this->assertNotEmpty($this->getUserDao()->findUnlockedUsersWithMobile(0, 10, false));
        $this->assertNotEmpty($this->getUserDao()->findUnlockedUsersWithMobile(0, 10, true));
    }

    public function testGetByVerifiedMobile()
    {
        $defaultUser = $this->getDefaultMockFields();
        $defaultUser['verifiedMobile'] = '13967340627';
        $this->getUserDao()->create($defaultUser);
        $user = $this->getUserDao()->getByVerifiedMobile('13967340627');
        $this->assertEquals($defaultUser['id'], $user['id']);

        $user = $this->getUserDao()->getByVerifiedMobile('13967340628');
        $this->assertEmpty($user);
    }

    public function testCountByLessThanCreatedTime()
    {
        $time = time();
        $defaultUser = $this->getDefaultMockFields();
        $defaultUser['type'] = 'default';
        $this->getUserDao()->create($defaultUser);

        $this->assertEquals(2, $this->getUserDao()->countByLessThanCreatedTime($time));
        $this->assertEquals(0, $this->getUserDao()->countByLessThanCreatedTime($time - 10));
    }

    public function testFindByNicknames()
    {
        $defaultUser = $this->getDefaultMockFields();
        $this->getUserDao()->create($defaultUser);
        $this->getUserDao()->create(array(
            'id' => '5',
            'nickname' => 'test2',
            'roles' => array('ROLE_ADMIN'),
            'password' => '3DMYb8GyEXk32ruFzw4lxy2elz6/aoPtA5X8vCTWezg=',
            'salt' => 'qunt972ow5c48k4wc8k0ss448os0oko',
            'email' => '800@qq.com',
            'type' => 'default',
        ));

        $users = $this->getUserDao()->findByNicknames(array('test', 'test2'));
        $this->assertEquals(2, count($users));

        $users = $this->getUserDao()->findByNicknames(array('test'));
        $this->assertEquals(1, count($users));
    }

    public function testFindByIds()
    {
        $defaultUser = $this->getDefaultMockFields();
        $this->getUserDao()->create($defaultUser);
        $this->getUserDao()->create(array(
            'id' => '5',
            'nickname' => 'test2',
            'roles' => array('ROLE_ADMIN'),
            'password' => '3DMYb8GyEXk32ruFzw4lxy2elz6/aoPtA5X8vCTWezg=',
            'salt' => 'qunt972ow5c48k4wc8k0ss448os0oko',
            'email' => '800@qq.com',
            'type' => 'default',
        ));

        $users = $this->getUserDao()->findByIds(array(3, 5));

        $this->assertCount(2, $users);
    }

    public function testGetByInviteCode()
    {
        $defaultUser = $this->getDefaultMockFields();
        $this->getUserDao()->create($defaultUser);

        $user = $this->getUserDao()->getByInviteCode($defaultUser['inviteCode']);

        $this->assertNotNull($user);
        $this->assertEquals($defaultUser['id'], $user['id']);
    }

    public function testWaveCounterById()
    {
        $defaultUser = $this->getDefaultMockFields();
        $this->getUserDao()->create($defaultUser);

        $this->getUserDao()->waveCounterById(3, 'newMessageNum', 2);
        $this->getUserDao()->waveCounterById(3, 'newNotificationNum', 5);

        $user = $this->getUserDao()->get($defaultUser['id']);
        $this->assertEquals(2, $user['newMessageNum']);
        $this->assertEquals(5, $user['newNotificationNum']);
    }

    public function testDeleteCounterById()
    {
        $defaultUser = $this->getDefaultMockFields();
        $defaultUser['newMessageNum'] = 3;
        $defaultUser['newNotificationNum'] = 6;

        $this->getUserDao()->create($defaultUser);

        $this->getUserDao()->deleteCounterById(3, 'newMessageNum');
        $this->getUserDao()->deleteCounterById(3, 'newNotificationNum');

        $user = $this->getUserDao()->get($defaultUser['id']);
        $this->assertEquals(0, $user['newMessageNum']);
        $this->assertEquals(0, $user['newNotificationNum']);
    }

    public function testAnalysisRegisterDataByTime()
    {
        $time = time();
        $defaultUser = $this->getDefaultMockFields();
        $defaultUser['type'] = 'default';
        $this->getUserDao()->create($defaultUser);
        $result = $this->getUserDao()->analysisRegisterDataByTime($time - 20, $time + 20);
        $this->assertEquals(2, $result[0]['count']);

        $result = $this->getUserDao()->analysisRegisterDataByTime($time - 3600 * 2, $time - 3600);
        $this->assertEmpty($result);
    }

    protected function getDefaultMockFields()
    {
        return array(
            'id' => '3',
            'nickname' => 'test',
            'roles' => array('ROLE_ADMIN'),
            'password' => '3DMYb8GyEXk32ruFzw4lxy2elz6/aoPtA5X8vCTWezg=',
            'salt' => 'qunt972ow5c48k4wc8k0ss448os0oko',
            'email' => '80@qq.com',
            'type' => 'system',
            'inviteCode' => 'test-code',
        );
    }

    private function getUserDao()
    {
        return $this->createDao('User:UserDao');
    }

    private function getUserProfileDao()
    {
        return $this->createDao('User:UserProfileDao');
    }
}
