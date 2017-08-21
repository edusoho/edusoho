<?php

namespace Tests;

use Codeages\Biz\Framework\Util\RandomToolkit;

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

    public function testCreateUserBalance()
    {
        $user = array(
            'user_id' => $this->biz['user']['id']
        );

        $userBalance = $this->getAccountService()->createUserBalance($user);
        $this->assertNotEmpty($userBalance);
        $this->assertEquals($user['user_id'], $userBalance['user_id']);
        $this->assertEquals(0, $userBalance['amount']);
        $this->assertEquals(0, $userBalance['cash_amount']);

        $userBalance = $this->getAccountService()->waveCashAmount($user['user_id'], 10);
        $this->assertEquals(10, $userBalance['cash_amount']);

        $userBalance = $this->getAccountService()->waveCashAmount($user['user_id'], -10);
        $this->assertEquals(0, $userBalance['cash_amount']);

        $userBalance = $this->getAccountService()->waveCashAmount($user['user_id'], -10);
        $this->assertEquals(-10, $userBalance['cash_amount']);

        $userBalance = $this->getAccountService()->waveAmount($user['user_id'], 10);
        $this->assertEquals(10, $userBalance['amount']);

        $userBalance = $this->getAccountService()->waveAmount($user['user_id'], -10);
        $this->assertEquals(0, $userBalance['amount']);

        $userBalance = $this->getAccountService()->waveAmount($user['user_id'], -10);
        $this->assertEquals(-10, $userBalance['amount']);
    }

    protected function getAccountService()
    {
        return $this->biz->service('Pay:AccountService');
    }
}