<?php

namespace Tests\Unit\User;

use Biz\BaseTestCase;
use AppBundle\Common\ReflectionUtils;

class InviteRecordServiceTest extends BaseTestCase
{
    public function testFindRecordsByInviteUserId()
    {
        $this->mockBiz(
            'User:InviteRecordDao',
            array(
                array(
                    'functionName' => 'findByInviteUserId',
                    'returnValue' => array('id' => 12, 'inviteUserId' => 22),
                    'withParams' => array(22),
                ),
            )
        );
        $result = $this->getInviteRecordService()->findRecordsByInviteUserId(22);
        $this->assertEquals(array('id' => 12, 'inviteUserId' => 22), $result);
    }

    public function testCreateInviteRecord()
    {
        $record = array(
            'inviteUserId' => 22,
            'invitedUserId' => 33,
            'inviteTime' => time(),
        );
        $this->mockBiz(
            'User:InviteRecordDao',
            array(
                array(
                    'functionName' => 'create',
                    'returnValue' => array('id' => 12, 'inviteUserId' => 22, 'invitedUserId' => 33),
                    'withParams' => array($record),
                ),
            )
        );
        $result = $this->getInviteRecordService()->createInviteRecord(22, 33);
        $this->assertEquals(array('id' => 12, 'inviteUserId' => 22, 'invitedUserId' => 33), $result);
    }

    public function testGetRecordByInvitedUserId()
    {
        $this->mockBiz(
            'User:InviteRecordDao',
            array(
                array(
                    'functionName' => 'getByInvitedUserId',
                    'returnValue' => array('id' => 12, 'inviteUserId' => 22, 'invitedUserId' => 33),
                    'withParams' => array(33),
                ),
            )
        );
        $result = $this->getInviteRecordService()->getRecordByInvitedUserId(33);
        $this->assertEquals(array('id' => 12, 'inviteUserId' => 22, 'invitedUserId' => 33), $result);
    }

    public function testFindByInvitedUserIds()
    {
        $this->mockBiz(
            'User:InviteRecordDao',
            array(
                array(
                    'functionName' => 'findByInvitedUserIds',
                    'returnValue' => array(array('id' => 12, 'inviteUserId' => 22, 'invitedUserId' => 33)),
                    'withParams' => array(array(33)),
                ),
            )
        );
        $result = $this->getInviteRecordService()->findByInvitedUserIds(array(33));
        $this->assertEquals(array('id' => 12, 'inviteUserId' => 22, 'invitedUserId' => 33), $result[0]);
    }

    public function testAddInviteRewardRecordToInvitedUser()
    {
        $this->mockBiz(
            'User:InviteRecordDao',
            array(
                array(
                    'functionName' => 'updateByInvitedUserId',
                    'returnValue' => array('id' => 12, 'inviteUserId' => 22, 'invitedUserId' => 33, 'amount' => 10),
                    'withParams' => array(22, array('amount' => 10)),
                ),
            )
        );
        $result = $this->getInviteRecordService()->addInviteRewardRecordToInvitedUser(22, array('amount' => 10));
        $this->assertEquals(array('id' => 12, 'inviteUserId' => 22, 'invitedUserId' => 33, 'amount' => 10), $result);
    }

    public function testCountRecords()
    {
        $this->mockBiz(
            'User:InviteRecordDao',
            array(
                array(
                    'functionName' => 'count',
                    'returnValue' => 5,
                    'withParams' => array(array('invitedUserId' => 22)),
                ),
            )
        );
        $result = $this->getInviteRecordService()->countRecords(array('invitedUserId' => 22));
        $this->assertEquals(5, $result);
    }

    public function testSearchRecords()
    {
        $this->mockBiz(
            'User:InviteRecordDao',
            array(
                array(
                    'functionName' => 'search',
                    'returnValue' => array(array('id' => 12, 'inviteUserId' => 22, 'invitedUserId' => 33)),
                    'withParams' => array(array('invitedUserId' => 22), array(), 0, 5),
                ),
            )
        );
        $result = $this->getInviteRecordService()->searchRecords(
            array('invitedUserId' => 22), 
            array(), 
            0, 
            5
        );
        $this->assertEquals(array('id' => 12, 'inviteUserId' => 22, 'invitedUserId' => 33), $result[0]);
    }

    public function testFlushOrderInfo()
    {
        $this->mockBiz(
            'User:InviteRecordDao',
            array(
                array(
                    'functionName' => 'search',
                    'returnValue' => array(array('id' => 12, 'inviteUserId' => 22, 'invitedUserId' => 33, 'inviteTime' => 50000)),
                    'withParams' => array(array('invitedUserId' => 22), array(), 0, 5),
                ),
                array(
                    'functionName' => 'update',
                    'returnValue' => array(array('id' => 12, 'amount' => 0.5)),
                    'withParams' => array(12, array('amount' => 0.5, 'cashAmount' => 0.2, 'coinAmount' => 0.3)),
                ),
            )
        );
        $this->mockBiz(
            'Order:OrderService',
            array(
                array(
                    'functionName' => 'sumPaidAmount',
                    'returnValue' => array('payAmount' => 50, 'cashAmount' => 20, 'coinAmount' => 30),
                    'withParams' => array(array('user_id' => 33, 'statuses' => array('success', 'finished'), 'pay_time_GT' => 50000)),
                ),
            )
        );
        $result = $this->getInviteRecordService()->flushOrderInfo(
            array('invitedUserId' => 22),
            0,
            5
        );
        $this->getInviteRecordDao()->shouldHaveReceived('update');
        $this->assertNull($result);
    }

    public function testFindByInviteUserIds()
    {
        $this->mockBiz(
            'User:InviteRecordDao',
            array(
                array(
                    'functionName' => 'findByInviteUserIds',
                    'returnValue' => array(array('id' => 12, 'inviteUserId' => 22, 'invitedUserId' => 33)),
                    'withParams' => array(array(22)),
                ),
            )
        );
        $result = $this->getInviteRecordService()->findByInviteUserIds(array(22));
        $this->assertEquals(array('id' => 12, 'inviteUserId' => 22, 'invitedUserId' => 33), $result[0]);
    }

    public function testUpdateOrderInfoById()
    {
        $this->mockBiz(
            'User:InviteRecordDao',
            array(
                array(
                    'functionName' => 'update',
                    'returnValue' => array('id' => 12, 'amount' => 50),
                    'withParams' => array(12, array('amount' => 0.5, 'cashAmount' => 0.2, 'coinAmount' => 0.3)),
                ),
            )
        );
        $result = $this->getInviteRecordService()->updateOrderInfoById(
            12,
            array('amount' => 0.5, 'cashAmount' => 0.2, 'coinAmount' => 0.3)
        );
        $this->assertEquals(array('id' => 12, 'amount' => 50), $result);
    }

    public function testGetOrderInfoByUserIdAndInviteTime()
    {
        $this->mockBiz(
            'Order:OrderService',
            array(
                array(
                    'functionName' => 'sumPaidAmount',
                    'returnValue' => array('payAmount' => 50, 'cashAmount' => 20, 'coinAmount' => 30),
                    'withParams' => array(array('user_id' => 33, 'statuses' => array('success', 'finished'), 'pay_time_GT' => 50000)),
                ),
            )
        );
        $result = $this->getInviteRecordService()->getOrderInfoByUserIdAndInviteTime(33, 50000);
        $this->assertEquals(array('payAmount' => 50, 'cashAmount' => 20, 'coinAmount' => 30), $result);
    }

    public function testGetAllUsersByRecords()
    {
        $records = array(array('id' => 2, 'inviteUserId' => 12, 'invitedUserId' => 22));
        $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'findUsersByIds',
                    'returnValue' => array(array('id' => 12, 'nickname' => 'test')),
                    'withParams' => array(array(12, 22)),
                ),
            )
        );
        $result = $this->getInviteRecordService()->getAllUsersByRecords($records);
        $this->assertEquals(array('id' => 12, 'nickname' => 'test'), $result[0]);
    }

    public function testSumCouponRateByInviteUserId()
    {
        $this->mockBiz(
            'User:InviteRecordDao',
            array(
                array(
                    'functionName' => 'sumCouponRateByInviteUserId',
                    'returnValue' => 6,
                    'withParams' => array(array(12, 22)),
                ),
            )
        );
        $result = $this->getInviteRecordService()->sumCouponRateByInviteUserId(array(12, 22));
        $this->assertEquals(6, $result);
    }

    public function testSearchRecordGroupByInviteUserId()
    {
        $this->mockBiz(
            'User:InviteRecordDao',
            array(
                array(
                    'functionName' => 'searchRecordGroupByInviteUserId',
                    'returnValue' => array(array('id' => 2, 'inviteUserId' => 12, 'invitedUserId' => 22)),
                    'withParams' => array(array('inviteUserId' => 12), 0, 5),
                ),
                array(
                    'functionName' => 'countPremiumUserByInviteUserIds',
                    'returnValue' => array('invitedUserCount' => 5, 'inviteUserId' => 12),
                    'withParams' => array(array(12)),
                ),
            )
        );
        $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'findUsersByIds',
                    'returnValue' => array(12 => array('id' => 12, 'nickname' => 'test')),
                    'withParams' => array(array(12)),
                ),
            )
        );
        $result = $this->getInviteRecordService()->searchRecordGroupByInviteUserId(array('inviteUserId' => 12), 0, 5);
        $this->assertEquals('test', $result[0]['invitedUserNickname']);
    }

    public function testCountPremiumUserByInviteUserIds()
    {
        $this->mockBiz(
            'User:InviteRecordDao',
            array(
                array(
                    'functionName' => 'countPremiumUserByInviteUserIds',
                    'returnValue' => array('invitedUserCount' => 5, 'inviteUserId' => 12),
                    'withParams' => array(array(12)),
                ),
            )
        );
        $result = $this->getInviteRecordService()->countPremiumUserByInviteUserIds(array(12));
        $this->assertEquals(array('invitedUserCount' => 5, 'inviteUserId' => 12), $result);
    }

    public function testCountInviteUser()
    {
        $this->mockBiz(
            'User:InviteRecordDao',
            array(
                array(
                    'functionName' => 'countInviteUser',
                    'returnValue' => 5,
                    'withParams' => array(array('inviteUserId' => 22)),
                ),
            )
        );
        $result = $this->getInviteRecordService()->countInviteUser(array('inviteUserId' => 22));
        $this->assertEquals(5, $result);
    }

    public function testPrepareConditions()
    {
        $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'searchUsers',
                    'returnValue' => array(array('id' => 12, 'nickname' => 'test')),
                    'withParams' => array(array('nickname' => 'test'), array('createdTime' => 'DESC'), 0, PHP_INT_MAX),
                ),
            )
        );
        $service = $this->getInviteRecordService();
        $conditions = array('nickname' => 'test', 'startDate' => '20171022', 'endDate' => '20171022');
        $result = ReflectionUtils::invokeMethod($service, '_prepareConditions', array($conditions));
        $this->assertEquals(array(12), $result['invitedUserIds']);
    }

    protected function getInviteRecordService()
    {
        return $this->createService('User:InviteRecordService');
    }

    protected function getInviteRecordDao()
    {
        return $this->createDao('User:InviteRecordDao');
    }
}