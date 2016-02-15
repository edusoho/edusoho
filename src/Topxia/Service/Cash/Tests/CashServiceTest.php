<?php
namespace Topxia\Service\Cash\Tests;

use Topxia\Service\Common\BaseTestCase;

class CashServiceTest extends BaseTestCase
{
    public function testSearchFlows()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $user    = $this->getUserService()->register($userInfo);
        $account = $this->getCashAccountService()->createAccount($user['id']);
        $this->getCashAccountService()->waveCashField($account['id'], '10000');

        $flow1 = $this->createInflowOrOutflow('inflow', $user['id'], 'RMB');
        $flow2 = $this->createInflowOrOutflow('outflow', $user['id'], 'RMB');
        $flow3 = $this->createInflowOrOutflow('outflow', $user['id'], 'Coin');
        $flow4 = $this->createInflowOrOutflow('inflow', $user['id'], 'RMB');

        $flows = $this->getCashService()->searchFlows(array('amount' => 10), array('id', 'ASC'), 0, PHP_INT_MAX);
        $this->assertEquals($flows[0]['type'], 'inflow');
        $this->assertEquals($flows[1]['type'], 'outflow');
        $this->assertEquals($flows[2]['cashType'], 'Coin');
        $this->assertEquals($flows[3]['cashType'], 'RMB');
    }

    public function testSearchFlowsCount()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $user    = $this->getUserService()->register($userInfo);
        $account = $this->getCashAccountService()->createAccount($user['id']);
        $this->getCashAccountService()->waveCashField($account['id'], '10000');

        $flow1 = $this->createInflowOrOutflow('inflow', $user['id'], 'RMB');
        $flow2 = $this->createInflowOrOutflow('inflow', $user['id'], 'RMB');
        $flow3 = $this->createInflowOrOutflow('outflow', $user['id'], 'RMB');
        $flow4 = $this->createInflowOrOutflow('inflow', $user['id'], 'RMB');

        $count = $this->getCashService()->searchFlowsCount(array('amount' => 10));
        $this->assertEquals(4, $count);
    }

    public function testInflowByCoin()
    {
        $inflow = array(
            'userId'      => 1,
            'type'        => 'inflow',
            'name'        => 'note6',
            'note'        => 'sumsung',
            'amount'      => 200.00,
            'cashType'    => 'Coin',
            'orderSn'     => 'V73263188923084',
            'payment'     => 'alipay',
            'category'    => 'guess',
            'createdTime' => time()
        );
        $inflow = $this->getCashService()->inflowByCoin($inflow);
        $this->assertEquals($inflow['type'], 'inflow');
        $this->assertEquals($inflow['cashType'], 'Coin');
        $this->assertEquals($inflow['name'], 'note6');
    }

    public function testOutflowByCoin()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $user    = $this->getUserService()->register($userInfo);
        $account = $this->getCashAccountService()->createAccount($user['id']);
        $this->getCashAccountService()->waveCashField($account['id'], '10000');

        $outflow = array(
            'userId'      => $user['id'],
            'type'        => 'outflow',
            'name'        => 'note6',
            'note'        => 'sumsung',
            'amount'      => 10.00,
            'cashType'    => 'Coin',
            'orderSn'     => 'V73263188923084',
            'payment'     => 'alipay',
            'category'    => 'guess',
            'createdTime' => time()
        );
        $outflow = $this->getCashService()->outflowByCoin($outflow);
        $this->assertEquals($outflow['type'], 'outflow');
        $this->assertEquals($outflow['cashType'], 'Coin');
        $this->assertEquals($outflow['name'], 'note6');
    }

    public function testInflowByRmb()
    {
        $inflow = array(
            'userId'      => 1,
            'type'        => 'inflow',
            'name'        => 'note6',
            'note'        => 'sumsung',
            'amount'      => 200.00,
            'cashType'    => 'RMB',
            'orderSn'     => 'V73263188923084',
            'payment'     => 'alipay',
            'category'    => 'gucci',
            'createdTime' => time()
        );
        $inflow = $this->getCashService()->inflowByRmb($inflow);
        $this->assertEquals($inflow['payment'], 'alipay');
        $this->assertEquals($inflow['cashType'], 'RMB');
        $this->assertEquals($inflow['name'], 'note6');
    }

    public function testOutflowByRmb()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $user    = $this->getUserService()->register($userInfo);
        $account = $this->getCashAccountService()->createAccount($user['id']);
        $this->getCashAccountService()->waveCashField($account['id'], '10000');

        $outflow = array(
            'userId'      => $user['id'],
            'type'        => 'outflow',
            'name'        => 'note6',
            'note'        => 'sumsung',
            'amount'      => 10.00,
            'cashType'    => 'RMB',
            'orderSn'     => 'V73263188923084',
            'payment'     => 'alipay',
            'category'    => 'guess',
            'createdTime' => time()
        );
        $outflow = $this->getCashService()->outflowByRmb($outflow);
        $this->assertEquals($outflow['type'], 'outflow');
        $this->assertEquals($outflow['cashType'], 'RMB');
        $this->assertEquals($outflow['name'], 'note6');
    }

    public function testChangeRmbToCoin()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $user    = $this->getUserService()->register($userInfo);
        $account = $this->getCashAccountService()->createAccount($user['id']);
        $this->getCashAccountService()->waveCashField($account['id'], '10000');

        $this->setSettingcoin();

        $rmbOutFlow = array(
            'userId'      => $user['id'],
            'type'        => 'outflow',
            'name'        => 'note6',
            'note'        => 'sumsung',
            'amount'      => 10.00,
            'cashType'    => 'RMB',
            'orderSn'     => 'V73263188923084',
            'payment'     => 'alipay',
            'category'    => 'guess',
            'createdTime' => time()
        );

        $inflow = $this->getCashService()->changeRmbToCoin($rmbOutFlow);
        $this->assertEquals($inflow['cashType'], 'Coin');
    }

    public function testAnalysisAmount()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $user    = $this->getUserService()->register($userInfo);
        $account = $this->getCashAccountService()->createAccount($user['id']);
        $this->getCashAccountService()->waveCashField($account['id'], '10000');

        $flow1 = $this->createInflowOrOutflow('inflow', $user['id'], 'RMB');
        $flow2 = $this->createInflowOrOutflow('outflow', $user['id'], 'RMB');
        $flow3 = $this->createInflowOrOutflow('outflow', $user['id'], 'Coin');
        $flow4 = $this->createInflowOrOutflow('inflow', $user['id'], 'RMB');

        $amount = $this->getCashService()->analysisAmount(array('cashType' => 'RMB', 'type' => 'inflow'));
        $this->assertEquals(20.00, $amount);
    }

    public function testFindUserIdsByFlows()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $user    = $this->getUserService()->register($userInfo);
        $account = $this->getCashAccountService()->createAccount($user['id']);
        $this->getCashAccountService()->waveCashField($account['id'], '10000');

        $userInfo2 = array(
            'nickname' => 'test_nickname2',
            'password' => 'test_password2',
            'email'    => 'test_email2@email.com'
        );
        $user2    = $this->getUserService()->register($userInfo2);
        $account2 = $this->getCashAccountService()->createAccount($user2['id']);
        $this->getCashAccountService()->waveCashField($account2['id'], '8888');

        $flow1 = $this->createInflowOrOutflow('inflow', $user['id'], 'RMB');
        $flow2 = $this->createInflowOrOutflow('outflow', $user['id'], 'RMB');
        $flow3 = $this->createInflowOrOutflow('outflow', $user['id'], 'Coin');
        $flow4 = $this->createInflowOrOutflow('inflow', $user2['id'], 'RMB');

        $userIdsFlow = $this->getCashService()->findUserIdsByFlows('inflow', '', 'DESC', 0, 100);
        $this->assertEquals($userIdsFlow[0]['amounts'], $userIdsFlow[1]['amounts']);
    }

    public function testFindUserIdsByFlowsCount()
    {
        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $user    = $this->getUserService()->register($userInfo);
        $account = $this->getCashAccountService()->createAccount($user['id']);
        $this->getCashAccountService()->waveCashField($account['id'], '10000');

        $userInfo2 = array(
            'nickname' => 'test_nickname2',
            'password' => 'test_password2',
            'email'    => 'test_email2@email.com'
        );
        $user2    = $this->getUserService()->register($userInfo2);
        $account2 = $this->getCashAccountService()->createAccount($user2['id']);
        $this->getCashAccountService()->waveCashField($account2['id'], '10000');

        $flow1 = $this->createInflowOrOutflow('inflow', $user['id'], 'RMB');
        $flow2 = $this->createInflowOrOutflow('outflow', $user['id'], 'Coin');
        $flow3 = $this->createInflowOrOutflow('inflow', $user2['id'], 'RMB');

        $userIdsCount = $this->getCashService()->findUserIdsByFlowsCount('inflow', time() - 3600);
        $this->assertEquals($userIdsCount, 2);
    }

    private function createInflowOrOutflow($flowType, $userId, $coinType)
    {
        $info = array(
            'userId'      => $userId,
            'type'        => $flowType,
            'name'        => 'note6',
            'note'        => 'sumsung',
            'amount'      => 10.00,
            'cashType'    => 'RMB',
            'orderSn'     => 'V73263188923084',
            'payment'     => 'alipay',
            'category'    => 'guess',
            'createdTime' => time()
        );

        switch ($flowType) {
            case 'inflow':

                if ($coinType == 'RMB') {
                    $flow = $this->getCashService()->inflowByRmb($info);
                } else {
                    $flow = $this->getCashService()->inflowByCoin($info);
                }

                break;

            case 'outflow':

                if ($coinType == 'RMB') {
                    $flow = $this->getCashService()->outflowByRmb($info);
                } else {
                    $flow = $this->getCashService()->outflowByCoin($info);
                }

                break;
        }

        return $flow;
    }

    private function setSettingcoin()
    {
        $coinSettingsPosted = array(
            'cash_rate' => '1.0',
            'coin_name' => 'coin'
        );
        $this->getSettingService()->set('coin', $coinSettingsPosted);
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getCashAccountService()
    {
        return $this->getServiceKernel()->createService('Cash.CashAccountService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getCashService()
    {
        return $this->getServiceKernel()->createService('Cash.CashService');
    }
}
