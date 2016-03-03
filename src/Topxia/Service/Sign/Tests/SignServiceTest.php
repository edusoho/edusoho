<?php
namespace Topxia\Service\Sign\Tests;

use Topxia\Service\Common\BaseTestCase;

class SignServiceTest extends BaseTestCase
{
    public function testUserSign()
    {
        $user = $this->createUser();
        $sign = $this->getSignService()->userSign($user['id'], 'classroom_sign', 1);
        $this->assertNotEmpty($sign);
        $this->assertEquals($user['id'], $sign['userId']);
        $this->assertEquals('classroom_sign', $sign['targetType']);
        $this->assertEquals('1', $sign['targetId']);
    }

    public function testIsSignedToday()
    {
        $user1 = $this->createUser();
        $user2 = $this->createUser();
        $this->getSignService()->userSign($user2['id'], 'classroom_sign', 1);
        $signToday1 = $this->getSignService()->isSignedToday($user1['id'], 'classroom_sign', 1);
        $signToday2 = $this->getSignService()->isSignedToday($user2['id'], 'classroom_sign', 1);
        $this->assertTrue($signToday1);
        $this->assertFalse($signToday2);
    }

    // public function testIsYestodaySigned()
    // {
    //     $user         = $this->createUser();
    //     $signYestoday = $this->getSignService()->isYestodaySigned($user['id'], 'classroom_sign', 1);
    //     $this->assertTrue($signYestoday);
    // }

    // public function testGetSignRecordsByPeriod()
    // {
    //     $user1    = array('id' => 1, 'nickname' => 'user', 'password' => 'user');
    //     $user2    = array('id' => 1, 'nickname' => 'user', 'password' => 'user');
    //     $getSign1 = $this->getSignService()->getSignRecordsByPeriod();
    // }

    // public function testgetSignUserStatistics()
    // {
    // }

    // public function testgetSignTargetStatistics()
    // {
    // }

    // public function testgetTodayRank()
    // {
    //     $user1 = array('id' => 1, 'nickname' => 'user', 'password' => 'user');
    //     $user2 = array('id' => 1, 'nickname' => 'user', 'password' => 'user');
    //     $rank1=$this->getSignService()->getTodayRank($user1['id'],'classroom_sign',1);
    //     $this->getSignService()->userSign($user2['id'], 'classroom_sign', 1);
    //     $rank2=$this->getSignService()->getTodayRank($user2['id'],'classroom_sign',1)
    // }

    private function createUser()
    {
        $user              = array();
        $user['id']        = 1;
        $user['email']     = "user@user.com";
        $user['nickname']  = "user";
        $user['password']  = "user";
        $user['currentIp'] = '127.0.0.1';
        $user['roles']     = array('ROLE_USER', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER');
        return $user;
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
