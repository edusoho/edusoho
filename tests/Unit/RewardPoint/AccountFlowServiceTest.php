<?php

namespace Tests\Unit\RewardPoint;

use Biz\BaseTestCase;

class AccountFlowServiceTest extends BaseTestCase
{
    public function testCreateAccountFlow()
    {
        $user = $this->createNormalUser();
        $account = array('userId' => $user['id']);
        $account = $this->getAccountService()->createAccount($account);

        $flow = array(
            'userId' => $account['userId'],
            'sn' => '00001',
            'type' => 'inflow',
            'amount' => 100,
            'operator' => 1,
        );

        $created = $this->getAccountFlowService()->createAccountFlow($flow);
        $this->assertEquals($flow['userId'], $created['userId']);
        $this->assertEquals($flow['sn'], $created['sn']);
    }

    /**
     *@expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testCreateAccountFlowWithoutFields()
    {
        $flow = array();
        $this->getAccountFlowService()->createAccountFlow($flow);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testCreateAccountFlowWithoutAccount()
    {
        $flow = array(
            'userId' => 999,
            'sn' => '00001',
            'type' => 'inflow',
            'amount' => 100,
            'operator' => 1,
        );

        $this->getAccountFlowService()->createAccountFlow($flow);
    }

    public function testUpdateAccountFlow()
    {
        $user = $this->createNormalUser();
        $account = array('userId' => $user['id']);
        $account = $this->getAccountService()->createAccount($account);

        $flow = array(
            'userId' => $account['userId'],
            'sn' => '00001',
            'type' => 'inflow',
            'amount' => 100,
            'operator' => 1,
        );

        $flow = $this->getAccountFlowService()->createAccountFlow($flow);

        $updated = $this->getAccountFlowService()->updateAccountFlow($flow['id'], array('amount' => 200));

        $this->assertEquals($updated['amount'], 200);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testUpdateAccountFlowWithoutAccount()
    {
        $user = $this->createNormalUser();
        $account = array('userId' => $user['id']);
        $account = $this->getAccountService()->createAccount($account);

        $flow = array(
            'userId' => $account['userId'],
            'sn' => '00001',
            'type' => 'inflow',
            'amount' => 100,
            'operator' => 1,
        );

        $flow = $this->getAccountFlowService()->createAccountFlow($flow);
        $this->getAccountFlowService()->updateAccountFlow($flow['id'], array('userId' => 999));
    }

    public function testDeleteAccountFlow()
    {
        $user = $this->createNormalUser();
        $account = array('userId' => $user['id']);
        $account = $this->getAccountService()->createAccount($account);

        $flow = array(
            'userId' => $account['userId'],
            'sn' => '00001',
            'type' => 'inflow',
            'amount' => 100,
            'operator' => 1,
        );

        $flow = $this->getAccountFlowService()->createAccountFlow($flow);
        $result = $this->getAccountFlowService()->deleteAccountFlow($flow['id']);
        $this->assertEquals($result, true);
    }

    public function getAccountFlow($id)
    {
        $user = $this->createNormalUser();
        $account = array('userId' => $user['id']);
        $account = $this->getAccountService()->createAccount($account);

        $flow = array(
            'userId' => $account['userId'],
            'sn' => '00001',
            'type' => 'inflow',
            'amount' => 100,
            'operator' => 1,
        );

        $flow = $this->getAccountFlowService()->createAccountFlow($flow);
        $flowInfo = $this->getAccountFlowService()->getAccountFlow($flow['id']);

        $this->assertEquals($flow, $flowInfo);
    }

    public function testSearchAccountFlows()
    {
        $user = $this->createNormalUser();
        $account = array('userId' => $user['id']);
        $account = $this->getAccountService()->createAccount($account);

        $flow = array(
            'userId' => $account['userId'],
            'sn' => '00001',
            'type' => 'inflow',
            'amount' => 100,
            'operator' => 1,
        );

        $flow = $this->getAccountFlowService()->createAccountFlow($flow);

        $flows = $this->getAccountFlowService()->searchAccountFlows(
            array(),
            array('createdTime' => 'DESC'),
            0, PHP_INT_MAX
        );

        $this->assertEquals(array_shift($flows), $flow);
    }

    public function testCountAccountFlows()
    {
        $user = $this->createNormalUser();
        $account = array('userId' => $user['id']);
        $account = $this->getAccountService()->createAccount($account);

        $flow = array(
            'userId' => $account['userId'],
            'sn' => '00001',
            'type' => 'inflow',
            'amount' => 100,
            'operator' => 1,
        );

        $this->getAccountFlowService()->createAccountFlow($flow);

        $count = $this->getAccountFlowService()->countAccountFlows(array());
        $this->assertEquals($count, 1);
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

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    protected function getAccountService()
    {
        return $this->createService('RewardPoint:AccountService');
    }

    protected function getAccountFlowService()
    {
        return $this->createService('RewardPoint:AccountFlowService');
    }
}
