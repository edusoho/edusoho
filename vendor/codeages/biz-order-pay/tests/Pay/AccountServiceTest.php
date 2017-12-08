<?php

namespace Tests;

use Codeages\Biz\Pay\Util\RandomToolkit;

class AccountServiceTest extends IntegrationTestCase
{
    public function setUp()
    {
        parent::setUp();

        $currentUser = array(
            'id' => 1
        );
        $this->biz['user'] = $currentUser;
    }

    public function testSetPayPassword()
    {
        $password = RandomToolkit::generateString();
        $account = $this->getAccountService()->setPayPassword($this->biz['user']['id'], $password);
        $this->assertNotEmpty($account);
        $this->assertNotEmpty($account['salt']);
        $this->assertNotEmpty($account['password']);
        $this->assertNotEmpty($account['created_time']);
        $this->assertNotEmpty($account['updated_time']);
        $this->assertEquals($this->biz['user']['id'], $account['user_id']);

        $this->assertTrue($this->getAccountService()->validatePayPassword($this->biz['user']['id'], $password));
        $this->assertTrue($this->getAccountService()->isPayPasswordSetted($this->biz['user']['id']));
    }

    public function testSetSecurityAnswer()
    {
        $answers = array(
            'key1' => 'key1 answer',
            'key2' => 'key2 answer',
            'key3' => 'key3 answer',
            'key4' => 'key4 answer',
        );
        $this->getAccountService()->setSecurityAnswers($this->biz['user']['id'], $answers);

        foreach ($answers as $key => $answer) {
            $this->assertTrue($this->getAccountService()->validateSecurityAnswer($this->biz['user']['id'], $key, $answer));
        }

        $this->assertTrue($this->getAccountService()->isSecurityAnswersSetted($this->biz['user']['id']));
    }

    public function testLockedAmount()
    {
        $user = array (
            'user_id' => $this->biz['user']['id'],
        );

        $userBalance = $this->getAccountService()->createUserBalance($user);

        $seller = array (
            'user_id' => 2
        );

        $seller = $this->getAccountService()->createUserBalance($seller);
        $recharge = array(
            'from_user_id' => $seller['user_id'],
            'to_user_id' => $userBalance['user_id'],
            'buyer_id' => $userBalance['user_id'],
            'amount' => '10',
            'title' => '充值1000个虚拟币',
            'action' => 'recharge'
        );

        $this->getAccountService()->transferCoin($recharge);

        $userBalance = $this->getAccountService()->lockCoin($user['user_id'], 5);

        $this->assertEquals(5, $userBalance['amount']);
        $this->assertEquals(5, $userBalance['locked_amount']);

        $userBalance = $this->getAccountService()->releaseCoin($user['user_id'], 3);
        $this->assertEquals(8, $userBalance['amount']);
        $this->assertEquals(2, $userBalance['locked_amount']);
    }

    public function testTransferCoin()
    {
        $user = array(
            'user_id' => 1
        );
        $buyer = $this->getAccountService()->createUserBalance($user);

        $user = array(
            'user_id' => 2
        );

        $seller = $this->getAccountService()->createUserBalance($user);
        $recharge = array(
            'to_user_id' => $buyer['user_id'],
            'from_user_id' => $seller['user_id'],
            'buyer_id' => $buyer['user_id'],
            'amount' => '1000',
            'amount_type' => 'coin',
            'title' => '充值1000个虚拟币',
            'action' => 'recharge'
        );

        $this->getAccountService()->transferCoin($recharge);

        $buyer = $this->getAccountService()->getUserBalanceByUserId($buyer['user_id']);
        $seller = $this->getAccountService()->getUserBalanceByUserId($seller['user_id']);

        $this->assertEquals(1000, $buyer['amount']);
        $this->assertEquals(-1000, $seller['amount']);
    }

    public function testWithdraw()
    {
        $user = array (
            'user_id' => $this->biz['user']['id'],
        );


        $userBalance = $this->getAccountService()->createUserBalance($user);
        $this->getAccountService()->rechargeCash(array(
            'user_id' => $this->biz['user']['id'],
            'cash_amount' => 100,
            'title' => 'test',
            'currency' => 'money',
            'platform' => 'cash',
            'trade_sn' => '112',
            'order_sn' => 123,
        ));

        $draw = array(
            'user_id' => $this->biz['user']['id'],
            'buyer_id' => $this->biz['user']['id'],
            'amount' => 100,
            'title' => '提现100',
            'currency' => 'CYN',
            'platform' => 'alipay',
        );
        $this->getAccountService()->withdrawCash($draw);
        $userBalance = $this->getAccountService()->getUserBalanceByUserId($userBalance['user_id']);

        $this->assertEquals(0, $userBalance['cash_amount']);
    }

    public function testSumAmountGroupByUserId()
    {
        $time = time();
        $cashFlow = array(
            'user_id' => 1,
            'sn' => '123',
            'amount_type' => 'money',
            'amount' => '3',
            'currency' => 'RMB',
            'order_sn' => 111,
            'trade_sn' => 111,
        );        
        $this->getCashflowDao()->create($cashFlow);
        $result = $this->getAccountService()->sumAmountGroupByUserId(array());
        $this->assertEquals(3, $result[1]['amount']);

        $result = $this->getAccountService()->sumAmountGroupByUserId(array('created_time_GT' => $time+1000));
        $this->assertTrue(empty($result));

        $cashFlow['user_id'] = 2;
        $cashFlow['amount'] = 4;
        $cashFlow = array_merge($cashFlow, array('user_id' => 2, 'amount' => 4, 'order_sn' => 222, 'trade_sn' => 222, 'sn' => 44));
        $this->getCashflowDao()->create($cashFlow);
        $result = $this->getAccountService()->sumAmountGroupByUserId(array());
        $this->assertEquals(4, $result[2]['amount']);

        $result = $this->getAccountService()->sumAmountGroupByUserId(array('user_ids' => array('1', '3', '5', '6')));
        $this->assertEquals(1, count($result));
        $this->assertEquals(3, $result[1]['amount']);
    }

    protected function getAccountService()
    {
        return $this->biz->service('Pay:AccountService');
    }

    protected function getCashflowDao()
    {
        return $this->biz->dao('Pay:CashflowDao');
    }
}