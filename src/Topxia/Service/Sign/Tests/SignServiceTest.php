<?php
namespace Topxia\Service\Sign\Tests;

use Topxia\Service\Common\BaseTestCase;

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
        $user         = $this->createUser('user');
        $signYestoday = $this->getSignService()->isYestodaySigned($user['id'], 'classroom_sign', 1);
        $this->assertFalse($signYestoday);
    }

    public function testGetSignRecordsByPeriod()
    {
        $today    = date("Y-m-d");
        $yestoday = date("Y-m-d", strtotime("-1 day"));
        $user1    = $this->createUser('user1');
        $user2    = $this->createUser('user2');
        $this->getSignService()->userSign($user2['id'], 'classroom_sign', 1);
        $getSign1 = $this->getSignService()->getSignRecordsByPeriod($user1['id'], 'classroom_sign', 1, $yestoday, $today);
        $getSign2 = $this->getSignService()->getSignRecordsByPeriod($user2['id'], 'classroom_sign', 1, $yestoday, $today);
        $this->assertNull($getSign1);
        $this->assertEquals($user2['id'], $getSign2[0]['userId']);
        $this->assertEquals('classroom_sign', $getSign2[0]['targetType']);
        $this->assertEquals('1', $getSign2[0]['targetId']);
    }

    // public function testgetSignUserStatistics()
    // {
    // }

    // public function testgetSignTargetStatistics()
    // {
    // }

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
        $userInfo             = array();
        $userInfo['email']    = "{$user}@{$user}.com";
        $userInfo['nickname'] = "{$user}";
        $userInfo['password'] = "{$user}";
        $userInfo['loginIp']  = '127.0.0.1';
        return $this->getUserService()->register($userInfo);
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getSignService()
    {
        return $this->getServiceKernel()->createService('Sign.SignService');
    }
}
