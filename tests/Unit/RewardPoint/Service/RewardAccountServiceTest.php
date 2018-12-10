<?php

namespace Tests\Unit\RewardPoint\Service;

use Biz\BaseTestCase;

class RewardAccountServiceTest extends BaseTestCase
{
    public function testCreateAccount()
    {
        $user = $this->createNormalUser();
        $account = array('userId' => $user['id']);
        $account = $this->getAccountService()->createAccount($account);

        $this->assertEquals($account['userId'], $user['id']);
    }

    /**
     *@expectedException \Biz\RewardPoint\AccountException
     */
    public function testCreateAccountUserRepeat()
    {
        $user = $this->createNormalUser();
        $account = array('userId' => $user['id']);
        $this->getAccountService()->createAccount($account);
        $this->getAccountService()->createAccount($account);
    }

    /**
     *@expectedException \Biz\Common\CommonException
     */
    public function testCreateAccountWithoutUserId()
    {
        $account = array();
        $this->getAccountService()->createAccount($account);
    }

    /**
     * @expectedException \Biz\User\UserException
     */
    public function testCreateAccountNotExistUser()
    {
        $account = array('userId' => 999);
        $this->getAccountService()->createAccount($account);
    }

    public function testUpdateAccount()
    {
        $user = $this->createNormalUser();
        $account = array('userId' => $user['id']);
        $account = $this->getAccountService()->createAccount($account);
        $updated = $this->getAccountService()->updateAccount($account['id'], array('balance' => 100));

        $this->assertEquals($updated['balance'], 100);
    }

    /**
     * @expectedException \Biz\RewardPoint\AccountException
     */
    public function testUpdateAccountWithoutAccount()
    {
        $this->getAccountService()->updateAccount(999, array('balance' => 100));
    }

    /**
     *@expectedException \Biz\RewardPoint\AccountException
     */
    public function testUpdateAccountUserNotCorrect()
    {
        $user = $this->createNormalUser();
        $account = array('userId' => $user['id']);
        $account = $this->getAccountService()->createAccount($account);
        $user1 = $this->createNormalUser1();
        $this->getAccountService()->updateAccount($account['id'], array('userId' => $user1['id']));
    }

    public function testDeleteAccount()
    {
        $user = $this->createNormalUser();
        $account = array('userId' => $user['id']);
        $account = $this->getAccountService()->createAccount($account);
        $result = $this->getAccountService()->deleteAccount($account['id']);

        $this->assertEquals($result, true);
    }

    /**
     * @expectedException \Biz\RewardPoint\AccountException
     */
    public function testDeleteAccountWithoutAccount()
    {
        $this->getAccountService()->deleteAccount(999);
    }

    public function testDeleteAccountByUserId()
    {
        $user = $this->createNormalUser();
        $account = array('userId' => $user['id']);
        $account = $this->getAccountService()->createAccount($account);
        $result = $this->getAccountService()->deleteAccountByUserId($account['userId']);

        $this->assertEquals($result, true);
    }

    /**
     * @expectedException \Biz\RewardPoint\AccountException
     */
    public function testDeleteAccountByUserIdWithoutAccount()
    {
        $this->getAccountService()->deleteAccountByUserId(99);
    }

    public function testGetAccount()
    {
        $user = $this->createNormalUser();
        $account = array('userId' => $user['id']);
        $account = $this->getAccountService()->createAccount($account);
        $accountInfo = $this->getAccountService()->getAccount($account['id']);

        $this->assertEquals($account, $accountInfo);
    }

    public function testGetAccountByUserId()
    {
        $user = $this->createNormalUser();
        $account = array('userId' => $user['id']);
        $account = $this->getAccountService()->createAccount($account);
        $accountInfo = $this->getAccountService()->getAccountByUserId($user['id']);

        $this->assertEquals($account, $accountInfo);
    }

    public function testSearchAccounts()
    {
        $user = $this->createNormalUser();
        $account = array('userId' => $user['id']);
        $account = $this->getAccountService()->createAccount($account);

        $accounts = $this->getAccountService()->searchAccounts(
            array(),
            array('createdTime' => 'DESC'),
            0, PHP_INT_MAX
        );

        $this->assertEquals(array_shift($accounts), $account);
    }

    public function testCountAccounts()
    {
        $user = $this->createNormalUser();
        $account = array('userId' => $user['id']);
        $this->getAccountService()->createAccount($account);

        $count = $this->getAccountService()->countAccounts(array());
        $this->assertEquals($count, 1);
    }

    public function testWaveBalance()
    {
        $user = $this->createNormalUser();
        $account = array('userId' => $user['id']);
        $account = $this->getAccountService()->createAccount($account);

        $this->getAccountService()->waveBalance($account['id'], 1000);

        $account = $this->getAccountService()->getAccount($account['id']);

        $this->assertEquals($account['balance'], 1000);
    }

    /**
     *@expectedException \Biz\Common\CommonException
     */
    public function testWaveBalanceWithErrorNum()
    {
        $user = $this->createNormalUser();
        $account = array('userId' => $user['id']);
        $account = $this->getAccountService()->createAccount($account);

        $this->getAccountService()->waveBalance($account['id'], 'kk');
    }

    /**
     * @expectedException \Biz\RewardPoint\AccountException
     */
    public function testWaveBalanceWithoutAccount()
    {
        $this->getAccountService()->waveBalance(99, 99.99);
    }

    public function testWaveDownBalance()
    {
        $user = $this->createNormalUser();
        $account = array('userId' => $user['id']);
        $account = $this->getAccountService()->createAccount($account);
        $this->getAccountService()->waveBalance($account['id'], 1000);

        $this->getAccountService()->waveDownBalance($account['id'], 100);

        $account = $this->getAccountService()->getAccount($account['id']);

        $this->assertEquals($account['balance'], 900);
    }

    /**
     *@expectedException \Biz\Common\CommonException
     */
    public function testWaveDownBalanceWithErrorNum()
    {
        $user = $this->createNormalUser();
        $account = array('userId' => $user['id']);
        $account = $this->getAccountService()->createAccount($account);
        $this->getAccountService()->waveBalance($account['id'], 1000);

        $this->getAccountService()->waveDownBalance($account['id'], 'kk');
    }

    /**
     * @expectedException \Biz\RewardPoint\AccountException
     */
    public function testWaveDownBalanceWithoutAccount()
    {
        $this->getAccountService()->waveDownBalance(99, 100);
    }

    public function testGrantRewardPoint()
    {
        $this->createNormalUser();
        $profile1 = array(
            'amount' => 11,
            'note' => 'testnote',
        );
        $profile2 = array(
            'amount' => 22,
            'note' => 'testnote',
        );
        $account = $this->getAccountService()->grantRewardPoint(1, $profile1);
        $this->assertEquals($account['balance'], 11);

        $account = array('userId' => 2, 'balance' => 33);
        $this->getAccountService()->createAccount($account);
        $account = $this->getAccountService()->grantRewardPoint(2, $profile2);
        $this->assertEquals($account['balance'], 55);
    }

    public function testDeductionRewardPoint()
    {
        $this->createNormalUser();
        $profile1 = array(
            'amount' => 0,
            'note' => 'testnote',
        );
        $profile2 = array(
            'amount' => 22,
            'note' => 'testnote',
        );
        $account = $this->getAccountService()->deductionRewardPoint(1, $profile1);
        $this->assertEquals($account['balance'], 0);

        $account = array('userId' => 2, 'balance' => 33);
        $this->getAccountService()->createAccount($account);
        $account = $this->getAccountService()->deductionRewardPoint(2, $profile2);
        $this->assertEquals($account['balance'], 11);
    }

    /**
     * @expectedException \Biz\RewardPoint\AccountException
     */
    public function testDeductionRewardPointWithErrorNum()
    {
        $this->createNormalUser();
        $profile = array(
            'amount' => 44,
            'note' => 'testnote',
        );
        $account = array('userId' => 2, 'balance' => 33);
        $this->getAccountService()->createAccount($account);
        $this->getAccountService()->deductionRewardPoint(2, $profile);
    }

    private function createNormalUser()
    {
        $user = array();
        $user['email'] = 'normal@user.com';
        $user['nickname'] = 'normal';
        $user['password'] = 'user';
        $user = $this->getUserService()->register($user);
        $user['currentIp'] = '127.0.0.1';
        $user['roles'] = array('ROLE_USER');

        return $user;
    }

    private function createNormalUser1()
    {
        $user = array();
        $user['email'] = 'normal1@user.com';
        $user['nickname'] = 'normal1';
        $user['password'] = 'user1';
        $user = $this->getUserService()->register($user);
        $user['currentIp'] = '127.0.0.1';
        $user['roles'] = array('ROLE_USER');

        return $user;
    }

    protected function getAccountService()
    {
        return $this->createService('RewardPoint:AccountService');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
