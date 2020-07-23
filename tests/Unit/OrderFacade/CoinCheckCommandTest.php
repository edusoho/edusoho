<?php

namespace Tests\Unit\OrderFacade;

use Biz\BaseTestCase;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Goods\Service\GoodsService;
use Biz\OrderFacade\Command\OrderPayCheck\CoinCheckCommand;
use Biz\OrderFacade\Command\OrderPayCheck\OrderPayChecker;
use Biz\OrderFacade\Exception\OrderPayCheckException;
use Biz\OrderFacade\Product\CourseProduct;
use Biz\OrderFacade\Service\OrderFacadeService;
use Biz\Product\Service\ProductService;
use Biz\System\Service\SettingService;
use Biz\User\CurrentUser;
use Biz\User\Dao\UserDao;
use Ramsey\Uuid\Uuid;

class CoinCheckCommandTest extends BaseTestCase
{
    public function testExecute_whenEmptyCoinAmount_thenReturnNull()
    {
        $command = new CoinCheckCommand();
        $command->setBiz($this->getBiz());
        $result = $command->execute(['pay_amount' => 1], []);

        $this->assertNull($result);
    }

    public function testExecute_whenCoinAmountNegative_thenThrowException()
    {
        $this->expectException(OrderPayCheckException::class);
        $this->expectExceptionMessage('order.pay_check_msg.coin_amount_error');

        $this->mockBiz('System:SettingService', [
            ['functionName' => 'get', 'returnValue' => ['coin_enabled' => 1, 'coin_name' => 'coin name', 'cash_rate' => 1, 'cash_model' => 'currency']],
        ]);
        $command = new CoinCheckCommand();
        $command->setBiz($this->getBiz());
        $result = $command->execute(['pay_amount' => 1], ['coinAmount' => -1]);
    }

    public function testExecute_whenEmptyPayPassword_thenThrowException()
    {
        $this->expectException(OrderPayCheckException::class);
        $this->expectExceptionMessage('order.pay_check_msg.missing_pay_password');

        $this->mockBiz('System:SettingService', [
            ['functionName' => 'get', 'returnValue' => ['coin_enabled' => 1, 'coin_name' => 'coin name', 'cash_rate' => 1, 'cash_model' => 'currency']],
        ]);
        $command = new CoinCheckCommand();
        $command->setBiz($this->getBiz());
        $result = $command->execute(['pay_amount' => 2], ['coinAmount' => 2]);
    }

    public function testExecute_whenBalanceAmount_thenThrowException()
    {
        $this->expectException(OrderPayCheckException::class);
        $this->expectExceptionMessage('order.pay_check_msg.balance_not_enough');

        $this->mockBiz('System:SettingService', [
            ['functionName' => 'get', 'returnValue' => ['coin_enabled' => 1, 'coin_name' => 'coin name', 'cash_rate' => 1, 'cash_model' => 'currency']],
        ]);
        $command = new CoinCheckCommand();
        $command->setBiz($this->getBiz());

        $this->mockBiz('Pay:AccountService', [
            [
                'functionName' => 'getUserBalanceByUserId',
                'returnValue' => ['amount' => 5],
            ],
        ]);

        $result = $command->execute(['pay_amount' => 10], ['coinAmount' => 10, 'payPassword' => '123456']);
    }

    public function testExecute_whenPayPasswordNotSetted_thenThrowException()
    {
        $this->expectException(OrderPayCheckException::class);
        $this->expectExceptionMessage('order.pay_check_msg.pay_password_not_set');

        $this->mockBiz('System:SettingService', [
            ['functionName' => 'get', 'returnValue' => ['coin_enabled' => 1, 'coin_name' => 'coin name', 'cash_rate' => 1, 'cash_model' => 'currency']],
        ]);
        $command = new CoinCheckCommand();
        $command->setBiz($this->getBiz());

        $this->mockBiz('Pay:AccountService', [
            [
                'functionName' => 'getUserBalanceByUserId',
                'returnValue' => ['amount' => 50],
            ],
            [
                'functionName' => 'isPayPasswordSetted',
                'returnValue' => false,
            ],
        ]);

        $result = $command->execute(['pay_amount' => 10], ['coinAmount' => 10, 'payPassword' => '123456']);
    }

    public function testExecute_whenPayPasswordNotValid_thenThrowException()
    {
        $this->expectException(OrderPayCheckException::class);
        $this->expectExceptionMessage('order.pay_check_msg.incorrect_pay_password');

        $this->mockBiz('System:SettingService', [
            ['functionName' => 'get', 'returnValue' => ['coin_enabled' => 1, 'coin_name' => 'coin name', 'cash_rate' => 1, 'cash_model' => 'currency']],
        ]);
        $command = new CoinCheckCommand();
        $command->setBiz($this->getBiz());

        $this->mockBiz('Pay:AccountService', [
            [
                'functionName' => 'getUserBalanceByUserId',
                'returnValue' => ['amount' => 50],
            ],
            [
                'functionName' => 'isPayPasswordSetted',
                'returnValue' => true,
            ],
            [
                'functionName' => 'validatePayPassword',
                'returnValue' => false,
            ],
        ]);

        $command->execute(['pay_amount' => 10], ['coinAmount' => 10, 'payPassword' => '123456']);
    }

    public function testExecute()
    {
        $this->mockBiz('System:SettingService', [
            ['functionName' => 'get', 'returnValue' => ['coin_enabled' => 1, 'coin_name' => 'coin name', 'cash_rate' => 1, 'cash_model' => 'currency', 'enable' => false]],
        ]);

        $this->mockBiz('Pay:AccountService', [
            [
                'functionName' => 'getUserBalanceByUserId',
                'returnValue' => ['amount' => 50],
            ],
            [
                'functionName' => 'isPayPasswordSetted',
                'returnValue' => true,
            ],
            [
                'functionName' => 'validatePayPassword',
                'returnValue' => true,
            ],
        ]);

        $checker = new OrderPayChecker();
        $checker->setBiz($this->getBiz());
        $checker->addCommand(new CoinCheckCommand());

        $course = $this->createCourse();

        $this->setNewCurrentUser();
        $order = $this->createCourseOrder($course['id']);

        $result = $checker->check($order, ['coinAmount' => 0, 'payPassword' => '123456']);

        $this->assertNull($result);
    }

    public function testExecute_whenCoinAmountError_thenThrowException()
    {
        $this->expectException(OrderPayCheckException::class);
        $this->expectExceptionMessage('order.pay_check_msg.out_of_max_coin');

        $course = $this->createCourse();
        $this->getSettingService()->set('coin', ['coin_enabled' => 1, 'coin_name' => 'coin name', 'cash_rate' => 1, 'cash_model' => 'currency']);

        $this->setNewCurrentUser();
        $order = $this->createCourseOrder($course['id']);

        $payChecker = new OrderPayChecker();
        $biz = $this->getBiz();
        $payChecker->setBiz($biz);
        $payChecker->addCommand(new CoinCheckCommand());

        $this->mockBiz('Pay:AccountService', [
            [
                'functionName' => 'getUserBalanceByUserId',
                'returnValue' => ['amount' => 10000],
            ],
            [
                'functionName' => 'isPayPasswordSetted',
                'returnValue' => true,
            ],
            [
                'functionName' => 'validatePayPassword',
                'returnValue' => true,
            ],
        ]);

        $result = $payChecker->check($order, ['coinAmount' => 10000, 'payPassword' => '123456']);
    }

    protected function createCourseOrder($courseId)
    {
        $courseProduct = $this->getProductService()->getProductByTargetIdAndType($courseId, 'course');
        $goodsSpecs = $this->getGoodsService()->getGoodsSpecsByProductIdAndTargetId($courseProduct['id'], $courseId);

        $product = new CourseProduct();
        $product->setBiz($this->getBiz());
        $product->init(['targetId' => $goodsSpecs['id']]);

        return $this->getOrderFacadeService()->create($product);
    }

    protected function createCourse($courseFields = [])
    {
        $courseFields = array_merge([
            'type' => 'normal',
            'title' => 'test course title',
            'about' => 'course about',
            'summary' => 'course summary',
            'price' => '100.00',
            'originPrice' => '100.00',
            'isFree' => 1,
            'buyable' => 1,
        ], $courseFields);

        $courseSet = $this->getCourseSetService()->createCourseSet($courseFields);

        $course = $this->getCourseService()->getCourse($courseSet['defaultCourseId']);

        $this->getCourseService()->updateCourse($course['id'], $courseFields);
        $this->getCourseService()->updateBaseInfo($course['id'], $courseFields);

        $this->getCourseSetService()->publishCourseSet($courseSet['id']);

        return $this->getCourseService()->getCourse($course['id']);
    }

    protected function setNewCurrentUser($newUser = [])
    {
        $newUser = array_merge([
            'nickname' => 'test_user',
            'type' => 'default',
            'email' => 'defaultUser@howzhi.com',
            'password' => '123123',
            'salt' => 'salt1',
            'roles' => ['ROLE_USER'],
            'uuid' => Uuid::uuid4(),
        ], $newUser);

        $user = $this->getUserDao()->create($newUser);

        $user['currentIp'] = '127.0.0.1';

        $currentUser = new CurrentUser();
        $this->getServiceKernel()->setCurrentUser($currentUser->fromArray($user));
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return UserDao
     */
    protected function getUserDao()
    {
        return $this->createDao('User:UserDao');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return OrderFacadeService
     */
    protected function getOrderFacadeService()
    {
        return $this->createService('OrderFacade:OrderFacadeService');
    }

    /**
     * @return ProductService
     */
    protected function getProductService()
    {
        return $this->createService('Product:ProductService');
    }

    /**
     * @return GoodsService
     */
    protected function getGoodsService()
    {
        return $this->createService('Goods:GoodsService');
    }
}
