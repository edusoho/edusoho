<?php
namespace Topxia\Service\Cash\Tests;

use Topxia\Service\Common\BaseTestCase;

class CashAccountServiceTest extends BaseTestCase
{
    public function testCreateAccount()
    {
        $user   = $this->createUser();
        $result = $this->getCashAccountService()->createAccount($user['id']);
        return $result;
    }

    public function testGetAccount()
    {
        $user    = $this->createUser();
        $account = $this->getCashAccountService()->createAccount($user['id']);
        $result  = $this->getCashAccountService()->getAccount($account['id']);
        return $result;
    }

    public function testGetAccountByUserId()
    {
        $user = $this->createUser();
        $this->getCashAccountService()->createAccount($user['id']);
        $result = $this->getCashAccountService()->getAccountByUserId($user['id']);
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
        return $result;
    }

    public function getSearchAccountCount()
    {
        $user = $this->createUser();
        $this->getCashAccountService()->createAccount($user['id']);
        $user2 = $this->createUser2();
        $this->getCashAccountService()->createAccount($user2['id']);
        $conditions = array(
            'userId' => $user['id']
        );
        $orderBy    = array('createdTime', 'Desc');
        $result     = $this->getCashAccountService()->SearchAccountCount($conditions, $orderBy, 0, 10);
        $conditions = array(
            'userId' => $user2['id']
        );
        $result     = $this->getCashAccountService()->SearchAccountCount($conditions, $orderBy, 0, 10);
        $conditions = array();
        $result     = $this->getCashAccountService()->SearchAccountCount($conditions, $orderBy, 0, 10);
        return $result;
    }

    public function testAddChange()
    {
        $user   = $this->createUser();
        $change = $this->getCashAccountService()->addChange($user['id']);
        return $change;
    }

    public function testGetChangeByUserId()
    {
        $user   = $this->createUser();
        $change = $this->getCashAccountService()->addChange($user['id']);
        $change = $this->getCashAccountService()->getChangeByUserId($user['id']);
        return $change;
    }

    public function testChangeCoin()
    {
        $coinSetting = $this->getSettingService()->set('coin', array('coin_name' => '虚拟币'));
        $user        = $this->createUser();
        $this->getCashAccountService()->createAccount($user['id']);
        $result = $this->getCashAccountService()->changeCoin(1, 1, $user['id']);
        return $result;
    }

    public function testWaveCashField()
    {
        $user    = $this->createUser();
        $account = $this->getCashAccountService()->createAccount($user['id']);
        $result  = $this->getCashAccountService()->waveCashField($account['id'], 1);
        return $result;
    }

    public function testWaveDownCashField()
    {
        $user    = $this->createUser();
        $account = $this->getCashAccountService()->createAccount($user['id']);
        $result  = $this->getCashAccountService()->waveDownCashField($account['id'], 1);
        return $result;
    }

    public function testReward()
    {
        $user    = $this->createUser();
        $account = $this->getCashAccountService()->createAccount($user['id']);
        $result  = $this->getCashAccountService()->reward(1, '送', $user['id'], null);
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
