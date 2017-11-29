<?php

namespace Tests\Unit\Marketing;

use Biz\BaseTestCase;
use Tests\Unit\Marketing\Tools\MockedMemberServiceImpl;

class MarketingServiceTest extends BaseTestCase
{
    public function testAddUserToCourse()
    {
        $postData = array(
            'mobile' => '13675641112',
            'user_id' => 12,
            'nickname' => 'test_user',
            'client_ip' => '127.2.3.21',
            'order_id' => 111,
            'order_price_amount' => 11000,
            'order_pay_amount' => 1,
            'activity_id' => 81,
            'activity_name' => '营销活动A',
            'deduct' => array(
                'id' => '2863',
                'order_id' => '2874',
                'detail' => '订单A',
                'item_id' => '2873',
                'deduct_type' => 'cut',
                'deduct_id' => '129555',
                'deduct_amount' => '10999',
                'status' => 'paid',
                'user_id' => '10000',
                'seller_id' => '1',
                'snapshot' => array(),
                'created_time' => '1511948304',
                'updated_time' => '1511948322',
            ),
            'target_type' => 'course',
            'target_id' => 12,
        );

        $user = $this->getUserDao()->create(
            array(
                'nickname' => 'defaultUser',
                'type' => 'system',
                'email' => 'defaultUser@howzhi.com',
                'password' => 'kaifazhe',
                'salt' => 'salt1',
                'roles' => array('ROLE_USER', 'ROLE_TEACHER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN'),
            )
        );

        $biz = $this->getBiz();
        $biz['@Marketing:MarketingMemberService'] = new MockedMemberServiceImpl($this->getBiz());

        $result = $this->getMarketingService()->addUserToCourse($postData);

        $this->assertEquals('3', $this->getMemberService()->getUserId());
        $this->assertEquals(12, $this->getMemberService()->getCourseId());
        $order = $this->getMemberService()->getOrder();

        $this->assertArrayEquals(
            array(
                'marketingOrderId' => 111,
                'marketingOrderPriceAmount' => 110,
                'marketingOrderPayAmount' => 0.01,
                'marketingActivityId' => 81,
                'marketingActivityName' => '营销活动A',
                'deducts' => array(
                    array(
                        'detail' => '订单A',
                        'deduct_type' => 'cut',
                        'deduct_amount' => 10999,
                        'user_id' => '3',
                    ),
                ),
                'originPrice' => 110,
                'price' => 0.01,
                'source' => 'marketing',
                'remark' => '来自微营销',
                'orderTitleRemark' => '(来自微营销)',
            ),
            $this->getMemberService()->getData()
        );

        $this->assertArrayEquals(
            array(
                'is_new' => true,
                'user_id' => '3',
                'code' => 'success',
                'msg' => '把用户,3添加到课程成功,课程ID：12,memberId:12222,订单Id:'.$order['id'],
            ),
            $result,
            array('is_new', 'user_id', 'code', 'msg')
        );

        $this->assertEquals('11000', $order['price_amount']);
        $this->assertEquals('1', $order['pay_amount']);
        $this->assertEquals('course', $order['create_extra']['targetType']);
    }

    protected function getMarketingService()
    {
        return $this->createService('Marketing:MarketingService');
    }

    protected function getUserDao()
    {
        return $this->createDao('User:UserDao');
    }

    protected function getMemberService()
    {
        return $this->createService('Marketing:MarketingMemberService');
    }
}
