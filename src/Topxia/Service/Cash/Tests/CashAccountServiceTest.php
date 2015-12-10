<?php
namespace Topxia\Service\Cash\Tests;

use Topxia\Service\Common\BaseTestCase;

class CashAccountServiceTest extends BaseTestCase
{
    public function testCreateAccount()
    {
        $user   = $this->createUser();
        $result = $this->getCashAccountService()->createAccount($user['id']);
        $this->assertEquals($result["cash"], 0);
        $this->assertEquals($result["userId"], $user['id']);
        return $result;
    }

    public function testGetAccount()
    {
        $user    = $this->createUser();
        $account = $this->getCashAccountService()->createAccount($user['id']);
        $result  = $this->getCashAccountService()->getAccount($account['id']);
        $this->assertEquals($result["cash"], 0);
        $this->assertEquals($result["userId"], $user['id']);
        return $result;
    }

    public function testGetAccountByUserId()
    {
        $user = $this->createUser();
        $this->getCashAccountService()->createAccount($user['id']);
        $result = $this->getCashAccountService()->getAccountByUserId($user['id']);
        $this->assertEquals($result["cash"], 0);
        $this->assertEquals($result["userId"], $user['id']);
        return $result;
    }

    public function testSearchAccount()
    {
        $user = $this->createUser();
        $this->getCashAccountService()->createAccount($user['id']);
        $user2 = $this->createUser2();
        $this->getCashAccountService()->createAccount($user2['id']);
        $conditions = array(
            'userId' => $user['id']
        );
        $orderBy    = array('createdTime', 'Desc');
        $result     = $this->getCashAccountService()->SearchAccount($conditions, $orderBy, 0, 10);
        $conditions = array(
            'userId' => $user2['id']
        );
        $result     = $this->getCashAccountService()->SearchAccount($conditions, $orderBy, 0, 10);
        $conditions = array();
        $result     = $this->getCashAccountService()->SearchAccount($conditions, $orderBy, 0, 10);
        $this->assertEquals($result[0]["cash"], 0);
        $this->assertEquals($result[0]["userId"], $user['id']);
        $this->assertEquals($result[1]["cash"], 0);
        $this->assertEquals($result[1]["userId"], $user2['id']);
        return $result;
    }

    public function testGetSearchAccountCount()
    {
        $user = $this->createUser();
        $this->getCashAccountService()->createAccount($user['id']);
        $user2 = $this->createUser2();
        $this->getCashAccountService()->createAccount($user2['id']);
        $conditions = array(
            'userId' => $user['id']
        );

        $orderBy = array('createdTime', 'Desc');
        $result  = $this->getCashAccountService()->SearchAccountCount($conditions, $orderBy, 0, 10);
        $this->assertEquals($result, 1);
        $conditions = array(
            'userId' => $user2['id']
        );
        $result = $this->getCashAccountService()->SearchAccountCount($conditions, $orderBy, 0, 10);
        $this->assertEquals($result, 1);
        $conditions = array();
        $result     = $this->getCashAccountService()->SearchAccountCount($conditions, $orderBy, 0, 10);
        $this->assertEquals($result, 2);
        return $result;
    }

    public function testAddChange()
    {
        $user   = $this->createUser();
        $change = $this->getCashAccountService()->addChange($user['id']);
        $this->assertEquals($change["amount"], 0);
        $this->assertEquals($change["userId"], $user['id']);
        return $change;
    }

    public function testGetChangeByUserId()
    {
        $user   = $this->createUser();
        $change = $this->getCashAccountService()->addChange($user['id']);
        $change = $this->getCashAccountService()->getChangeByUserId($user['id']);
        $this->assertEquals($change["amount"], 0);
        $this->assertEquals($change["userId"], $user['id']);
        return $change;
    }

    public function testChangeCoin()
    {
        $coinSetting = $this->getSettingService()->set('coin', array('coin_name' => '虚拟币'));
        $user        = $this->createUser();
        $this->getCashAccountService()->createAccount($user['id']);
        $result = $this->getCashAccountService()->changeCoin(1, 1, $user['id']);
        $this->assertEquals($result["amount"], 1);
        $this->assertEquals($result["userId"], $user['id']);
        return $result;
    }

    public function testWaveCashField()
    {
        $user    = $this->createUser();
        $account = $this->getCashAccountService()->createAccount($user['id']);
        $this->assertEquals($account['cash'], 0);
        $this->assertEquals($account["userId"], $user['id']);
        $this->getCashAccountService()->waveCashField($account['id'], 1);
        $account = $this->getCashAccountService()->getAccount($account['id']);
        $this->assertEquals($account['cash'], 1);
        $this->assertEquals($account["userId"], $user['id']);
        return $account;
    }

    public function testWaveDownCashField()
    {
        $user    = $this->createUser();
        $account = $this->getCashAccountService()->createAccount($user['id']);
        $this->assertEquals($account['cash'], 0);
        $this->assertEquals($account["userId"], $user['id']);
        $this->getCashAccountService()->waveDownCashField($account['id'], 1);
        $account = $this->getCashAccountService()->getAccount($account['id']);
        $this->assertEquals($account['cash'], -1);
        $this->assertEquals($account["userId"], $user['id']);
        return $account;
    }

    public function testReward()
    {
        $user    = $this->createUser();
        $account = $this->getCashAccountService()->createAccount($user['id']);
        $result  = $this->getCashAccountService()->reward(1, '送', $user['id'], null);
        $this->assertEquals($result['amount'], 1);
        $this->assertEquals($account["userId"], $user['id']);
        return $result;
    }

    protected function getCashAccountService()
    {
        return $this->getServiceKernel()->createService('Cash.CashAccountService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function createUser()
    {
        $user             = array();
        $user['email']    = "user@user.com";
        $user['nickname'] = "user";
        $user['password'] = "user";
        return $this->getUserService()->register($user);
    }

    protected function createUser2()
    {
        $user             = array();
        $user['email']    = "user2@user.com";
        $user['nickname'] = "user2";
        $user['password'] = "user2";
        return $this->getUserService()->register($user);
    }
}
