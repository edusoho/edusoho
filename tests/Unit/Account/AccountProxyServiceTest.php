<?php

namespace Tests\Unit\Account;

use Biz\BaseTestCase;

class AccountProxyServiceTest extends BaseTestCase
{
    public function testPrepareConditions()
    {
        $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'getUserByNickname',
                    'returnValue' => array('id' => 11),
                    'withParams' => array('nickname'),
                ),
            )
        );
        $this->mockBiz(
            'Pay:PayService',
            array(
                array(
                    'functionName' => 'getTradeByPlatformSn',
                    'returnValue' => array('trade_sn' => '2017112210340978420'),
                    'withParams' => array('2017112210340968430'),
                ),
            )
        );
        $result = $this->getAccountProxyService()->prepareConditions(array(
            'startTime' => '2017-12-02 07:30',
            'endTime' => '2017-12-02 07:30',
            'keyword' => '201711221034096',
            'keywordType' => 'sn',
            'nickname' => 'nickname',
            'buyerNickname' => 'nickname',
            'platform_sn' => '2017112210340968430',
            'platform' => 'none'
        ));

        $this->assertEquals('2017112210340978420', $result['trade_sn']);
    }

    public function testPrepareConditionsWithCoinPlatform()
    {
        $result = $this->getAccountProxyService()->prepareConditions(array(
            'startTime' => '2017-12-02 07:30',
            'endTime' => '2017-12-02 07:30',
            'keyword' => '201711221034096',
            'keywordType' => 'sn',
            'nickname' => 'nickname',
            'buyerNickname' => 'nickname',
            'platform_sn' => '2017112210340968430',
            'platform' => 'coin'
        ));

        $this->assertEquals('inflow', $result['type']);
    }

    public function testCountUsersByConditions()
    {
        $this->mockBiz(
            'Pay:CashflowDao',
            array(
                array(
                    'functionName' => 'countUsersByConditions',
                    'returnValue' => 10,
                    'withParams' => array(array(
                        'platform' => 'coin',
                        'created_time_GTE' => 1512171000,
                        'created_time_LT' => 1512171000,
                        'sn' => '201711221034096',
                        'user_id' => -1,
                        'buyer_id' => -1,
                        'trade_sn' => 0,
                        'type' => 'inflow',
                    )),
                ),
            )
        );
        $result = $this->getAccountProxyService()->countUsersByConditions(array(
            'startTime' => '2017-12-02 07:30',
            'endTime' => '2017-12-02 07:30',
            'keyword' => '201711221034096',
            'keywordType' => 'sn',
            'nickname' => 'nickname',
            'buyerNickname' => 'nickname',
            'platform_sn' => '2017112210340968430',
            'platform' => 'coin'
        ));

        $this->assertEquals(10, $result);
    }

    public function testCountCashflows()
    {
        $this->mockBiz(
            'Pay:CashflowDao',
            array(
                array(
                    'functionName' => 'count',
                    'returnValue' => 10,
                    'withParams' => array(array(
                        'platform' => 'coin',
                        'created_time_GTE' => 1512171000,
                        'created_time_LT' => 1512171000,
                        'sn' => '201711221034096',
                        'user_id' => -1,
                        'buyer_id' => -1,
                        'trade_sn' => 0,
                        'type' => 'inflow',
                    )),
                ),
            )
        );
        $result = $this->getAccountProxyService()->countCashflows(array(
            'startTime' => '2017-12-02 07:30',
            'endTime' => '2017-12-02 07:30',
            'keyword' => '201711221034096',
            'keywordType' => 'sn',
            'nickname' => 'nickname',
            'buyerNickname' => 'nickname',
            'platform_sn' => '2017112210340968430',
            'platform' => 'coin'
        ));

        $this->assertEquals(10, $result);
    }

    public function testSearchCashflows()
    {
        $this->mockBiz(
            'Pay:CashflowDao',
            array(
                array(
                    'functionName' => 'search',
                    'returnValue' => array(array('id' => 11)),
                    'withParams' => array(
                        array(
                            'platform' => 'coin',
                            'created_time_GTE' => 1512171000,
                            'created_time_LT' => 1512171000,
                            'sn' => '201711221034096',
                            'user_id' => -1,
                            'buyer_id' => -1,
                            'trade_sn' => 0,
                            'type' => 'inflow',
                        ),
                        array(),
                        0,
                        5,
                    ),
                ),
            )
        );
        $result = $this->getAccountProxyService()->searchCashflows(
            array(
                'startTime' => '2017-12-02 07:30',
                'endTime' => '2017-12-02 07:30',
                'keyword' => '201711221034096',
                'keywordType' => 'sn',
                'nickname' => 'nickname',
                'buyerNickname' => 'nickname',
                'platform_sn' => '2017112210340968430',
                'platform' => 'coin'
            ),
            array(),
            0,
            5
        );

        $this->assertEquals(array(array('id' => 11)), $result);
    }

    public function testSumColumnByConditions()
    {
        $this->mockBiz(
            'Pay:CashflowDao',
            array(
                array(
                    'functionName' => 'sumColumnByConditions',
                    'returnValue' => '+0.1',
                    'withParams' => array(
                        'amount',
                        array(
                            'platform' => 'coin',
                            'created_time_GTE' => 1512171000,
                            'created_time_LT' => 1512171000,
                            'sn' => '201711221034096',
                            'user_id' => -1,
                            'buyer_id' => -1,
                            'trade_sn' => 0,
                            'type' => 'inflow',
                        ),
                    ),
                ),
            )
        );
        $result = $this->getAccountProxyService()->sumColumnByConditions(
            'amount',
            array(
                'startTime' => '2017-12-02 07:30',
                'endTime' => '2017-12-02 07:30',
                'keyword' => '201711221034096',
                'keywordType' => 'sn',
                'nickname' => 'nickname',
                'buyerNickname' => 'nickname',
                'platform_sn' => '2017112210340968430',
                'platform' => 'coin'
            )
        );

        $this->assertEquals('+0.1', $result);
    }

    protected function getAccountProxyService()
    {
        return $this->createService('Account:AccountProxyService');
    }
}