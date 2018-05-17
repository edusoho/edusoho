<?php

namespace Tests\Unit\Sign\Service;

use Biz\BaseTestCase;
use Biz\Sign\Service\SignService;

class SignServiceTest extends BaseTestCase
{
    public function testUserSign()
    {
        $user = $this->createUser('user');
        $sign = $this->getSignService()->userSign($user['id'], 'classroom_sign', 1);
        $this->assertNotEmpty($sign);
        $this->assertEquals($user['id'], $sign['userId']);
        $this->assertEquals('classroom_sign', $sign['targetType']);
        $this->assertEquals('1', $sign['targetId']);
    }

    public function testIsSignedToday()
    {
        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');
        $this->getSignService()->userSign($user2['id'], 'classroom_sign', 1);
        $signToday1 = $this->getSignService()->isSignedToday($user1['id'], 'classroom_sign', 1);
        $signToday2 = $this->getSignService()->isSignedToday($user2['id'], 'classroom_sign', 1);
        $this->assertFalse($signToday1);
        $this->assertTrue($signToday2);
    }

    public function testIsYestodaySigned()
    {
        $user = $this->createUser('user');
        $signYestoday = $this->getSignService()->isYestodaySigned($user['id'], 'classroom_sign', 1);
        $this->assertFalse($signYestoday);
    }

    public function testGetSignRecordsByPeriod()
    {
        $today = date('Y-m-d');
        $yestoday = date('Y-m-d', strtotime('-1 day'));
        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');
        $this->getSignService()->userSign($user2['id'], 'classroom_sign', 1);
        $getSign1 = $this->getSignService()->findSignRecordsByPeriod($user1['id'], 'classroom_sign', 1, $yestoday, $today);
        $getSign2 = $this->getSignService()->findSignRecordsByPeriod($user2['id'], 'classroom_sign', 1, $yestoday, $today);
        $this->assertEmpty($getSign1);
        $this->assertEquals($user2['id'], $getSign2[0]['userId']);
        $this->assertEquals('classroom_sign', $getSign2[0]['targetType']);
        $this->assertEquals('1', $getSign2[0]['targetId']);
    }

    public function testgetSignUserStatistics()
    {
        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');
        $this->getSignService()->userSign($user2['id'], 'classroom_sign', 1);
        $sign1 = $this->getSignService()->getSignUserStatistics($user1['id'], 'classroom_sign', 1);
        $sign2 = $this->getSignService()->getSignUserStatistics($user2['id'], 'classroom_sign', 1);
        $this->assertNull($sign1);
        $this->assertEquals($user2['id'], $sign2['userId']);
        $this->assertEquals('classroom_sign', $sign2['targetType']);
        $this->assertEquals('1', $sign2['targetId']);
    }

    public function testgetSignTargetStatistics()
    {
        $user = $this->createUser('user');
        $sign = $this->getSignService()->userSign($user['id'], 'classroom_sign', 1);
        $time = date('Ymd', $sign['createdTime']);
        $signTarget = $this->getSignService()->getSignTargetStatistics('classroom_sign', 1, $time);
        $this->assertEquals($time, $signTarget['date']);
        $this->assertEquals('classroom_sign', $signTarget['targetType']);
        $this->assertEquals('1', $signTarget['targetId']);
        $this->assertEquals('1', $signTarget['signedNum']);
    }

    public function testgetTodayRank()
    {
        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');
        $rank1 = $this->getSignService()->getTodayRank($user1['id'], 'classroom_sign', 1);
        $this->getSignService()->userSign($user2['id'], 'classroom_sign', 1);
        $rank2 = $this->getSignService()->getTodayRank($user2['id'], 'classroom_sign', 1);
        $this->assertEquals('-1', $rank1);
        $this->assertEquals('1', $rank2);
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

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return SignService
     */
    protected function getSignService()
    {
        return $this->createService('Sign:SignService');
    }
}
