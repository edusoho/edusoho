<?php

namespace Tests\Unit\OrderFacade;

use Biz\BaseTestCase;
use Biz\OrderFacade\Command\OrderPayCheck\CoinCheckCommand;
use Biz\OrderFacade\Command\OrderPayCheck\OrderPayChecker;

class CoinCheckCommandTest extends BaseTestCase
{
    public function testExecuteEmptyCoinAmount()
    {
        $command = new CoinCheckCommand();
        $command->setBiz($this->getBiz());
        $result = $command->execute(array('pay_amount' => 1), array());

        $this->assertEmpty($result);
    }

    /**
     * @expectedException \Biz\OrderFacade\Exception\OrderPayCheckException
     * @expectedExceptionMessage order.pay_check_msg.coin_amount_error
     */
    public function testExecuteCoinAmountNegative()
    {
        $this->mockBiz('System:SettingService', array(
            array('functionName' => 'get', 'returnValue' => array('coin_enabled' => 1, 'coin_name' => 'coin name', 'cash_rate' => 1, 'cash_model' => 'currency')),
        ));
        $command = new CoinCheckCommand();
        $command->setBiz($this->getBiz());
        $result = $command->execute(array('pay_amount' => 1), array('coinAmount' => -1));
    }

    /**
     * @expectedException \Biz\OrderFacade\Exception\OrderPayCheckException
     * @expectedExceptionMessage order.pay_check_msg.missing_pay_password
     */
    public function testExecuteEmptyPayPassword()
    {
        $this->mockBiz('System:SettingService', array(
            array('functionName' => 'get', 'returnValue' => array('coin_enabled' => 1, 'coin_name' => 'coin name', 'cash_rate' => 1, 'cash_model' => 'currency')),
        ));
        $command = new CoinCheckCommand();
        $command->setBiz($this->getBiz());
        $result = $command->execute(array('pay_amount' => 2), array('coinAmount' => 2));
    }

    /**
     * @expectedException \Biz\OrderFacade\Exception\OrderPayCheckException
     * @expectedExceptionMessage order.pay_check_msg.balance_not_enough
     */
    public function testExecuteBalanceAmount()
    {
        $this->mockBiz('System:SettingService', array(
            array('functionName' => 'get', 'returnValue' => array('coin_enabled' => 1, 'coin_name' => 'coin name', 'cash_rate' => 1, 'cash_model' => 'currency')),
        ));
        $command = new CoinCheckCommand();
        $command->setBiz($this->getBiz());

        $this->mockBiz('Pay:AccountService', array(
            array(
                'functionName' => 'getUserBalanceByUserId',
                'returnValue' => array('amount' => 5),
            ),
        ));

        $result = $command->execute(array('pay_amount' => 10), array('coinAmount' => 10, 'payPassword' => '123456'));
    }

    /**
     * @expectedException \Biz\OrderFacade\Exception\OrderPayCheckException
     * @expectedExceptionMessage order.pay_check_msg.pay_password_not_set
     */
    public function testExecutePayPasswordSetted()
    {
        $this->mockBiz('System:SettingService', array(
            array('functionName' => 'get', 'returnValue' => array('coin_enabled' => 1, 'coin_name' => 'coin name', 'cash_rate' => 1, 'cash_model' => 'currency')),
        ));
        $command = new CoinCheckCommand();
        $command->setBiz($this->getBiz());

        $this->mockBiz('Pay:AccountService', array(
            array(
                'functionName' => 'getUserBalanceByUserId',
                'returnValue' => array('amount' => 50),
            ),
            array(
                'functionName' => 'isPayPasswordSetted',
                'returnValue' => false,
            ),
        ));

        $result = $command->execute(array('pay_amount' => 10), array('coinAmount' => 10, 'payPassword' => '123456'));
    }

    /**
     * @expectedException \Biz\OrderFacade\Exception\OrderPayCheckException
     * @expectedExceptionMessage order.pay_check_msg.incorrect_pay_password
     */
    public function testExecuteValidatePayPassword()
    {
        $this->mockBiz('System:SettingService', array(
            array('functionName' => 'get', 'returnValue' => array('coin_enabled' => 1, 'coin_name' => 'coin name', 'cash_rate' => 1, 'cash_model' => 'currency')),
        ));
        $command = new CoinCheckCommand();
        $command->setBiz($this->getBiz());

        $this->mockBiz('Pay:AccountService', array(
            array(
                'functionName' => 'getUserBalanceByUserId',
                'returnValue' => array('amount' => 50),
            ),
            array(
                'functionName' => 'isPayPasswordSetted',
                'returnValue' => true,
            ),
            array(
                'functionName' => 'validatePayPassword',
                'returnValue' => false,
            ),
        ));

        $result = $command->execute(array('pay_amount' => 10), array('coinAmount' => 10, 'payPassword' => '123456'));
    }

    public function testExecute()
    {
        $payChecker = new OrderPayChecker();
        $biz = $this->getBiz();
        $payChecker->setBiz($biz);
        $payChecker->addCommand(new CoinCheckCommand());

        $this->mockBiz('System:SettingService', array(
            array('functionName' => 'get', 'returnValue' => array('coin_enabled' => 1, 'coin_name' => 'coin name', 'cash_rate' => 1, 'cash_model' => 'currency')),
        ));

        $this->mockBiz('Pay:AccountService', array(
            array(
                'functionName' => 'getUserBalanceByUserId',
                'returnValue' => array('amount' => 50),
            ),
            array(
                'functionName' => 'isPayPasswordSetted',
                'returnValue' => true,
            ),
            array(
                'functionName' => 'validatePayPassword',
                'returnValue' => true,
            ),
        ));

        $this->_mockCourseProduct();

        $this->mockBiz('Order:OrderService', array(
            array('functionName' => 'findOrderItemsByOrderId',
                'returnValue' => array(array('target_type' => 'course', 'target_id' => 1, 'num' => 1, 'unit' => '')),
            ),
        ));

        $result = $payChecker->check(array('id' => 123, 'pay_amount' => 10), array('coinAmount' => 10, 'payPassword' => '123456'));

        $this->assertNull($result);
    }

    /**
     * @expectedException \Biz\OrderFacade\Exception\OrderPayCheckException
     * @expectedExceptionMessage order.pay_check_msg.out_of_max_coin
     */
    public function testExecuteCoinAmountError()
    {
        $payChecker = new OrderPayChecker();
        $biz = $this->getBiz();
        $payChecker->setBiz($biz);
        $payChecker->addCommand(new CoinCheckCommand());

        $this->mockBiz('System:SettingService', array(
            array('functionName' => 'get', 'returnValue' => array('coin_enabled' => 1, 'coin_name' => 'coin name', 'cash_rate' => 1, 'cash_model' => 'currency')),
        ));

        $this->mockBiz('Pay:AccountService', array(
            array(
                'functionName' => 'getUserBalanceByUserId',
                'returnValue' => array('amount' => 1000),
            ),
            array(
                'functionName' => 'isPayPasswordSetted',
                'returnValue' => true,
            ),
            array(
                'functionName' => 'validatePayPassword',
                'returnValue' => true,
            ),
        ));

        $this->_mockCourseProduct();

        $this->mockBiz('Order:OrderService', array(
            array('functionName' => 'findOrderItemsByOrderId',
                'returnValue' => array(array('target_type' => 'course', 'target_id' => 1, 'num' => 1, 'unit' => '')),
            ),
        ));

        $result = $payChecker->check(array('id' => 123, 'pay_amount' => 1), array('coinAmount' => 1000, 'payPassword' => '123456'));
    }

    private function _mockCourseProduct()
    {
        $this->mockBiz('Course:CourseService', array(
            array(
                'functionName' => 'getCourse',
                'returnValue' => array(
                    'id' => 1,
                    'title' => 'course title',
                    'courseSetId' => 1,
                    'price' => 100,
                    'originPrice' => 100,
                    'maxRate' => 100,
                    'status' => 'published',
                ),
            ),
        ));

        $this->mockBiz('Course:CourseSetService', array(
            array(
                'functionName' => 'getCourseSet',
                'returnValue' => array('id' => 1, 'title' => 'course_set title', 'cover' => array(), 'status' => 'published'),
            ),
        ));
    }
}
