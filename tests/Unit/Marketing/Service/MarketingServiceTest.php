<?php

namespace Tests\Unit\Marketing\Service;

use AppBundle\Common\TimeMachine;
use Biz\BaseTestCase;
use Tests\Unit\Marketing\Tools\MockedClassroomMemberServiceImpl;
use Tests\Unit\Marketing\Tools\MockedCourseMemberServiceImpl;

class MarketingServiceTest extends BaseTestCase
{
    public function testAddUserToCourse()
    {
        TimeMachine::setMockedTime(1517401609);

        $mockedCourseService = $this->mockBiz(
            'Course:CourseService',
            array(
                array(
                    'functionName' => 'getCourse',
                    'withParams' => array(12),
                    'returnValue' => array(
                        'title' => '',
                        'courseSetId' => 3,
                        'courseSetTitle' => 'CourseSet',
                        'id' => 12,
                        'price' => 12,
                        'originPrice' => 12,
                        'status' => 'published',
                        'maxRate' => 1,
                    ),
                ),
            )
        );

        $mockedCourseSetService = $this->mockBiz(
            'Course:CourseSetService',
            array(
                array(
                    'functionName' => 'getCourseSet',
                    'withParams' => array(3),
                    'returnValue' => array('cover' => 'cover', 'status' => 'published'),
                ),
            )
        );

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
            'order_pay_time' => TimeMachine::time(),
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

        $this->getSettingService()->set('refund', array('maxRefundDays' => 2));

        $biz = $this->getBiz();
        $biz['@Marketing:MarketingCourseMemberService'] = new MockedCourseMemberServiceImpl($this->getBiz());

        $result = $this->getMarketingCourseService()->join($postData);

        $this->assertEquals('3', $this->getCourseMemberService()->getUserId());
        $this->assertEquals(12, $this->getCourseMemberService()->getCourseId());
        $order = $this->getCourseMemberService()->getOrder();

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
            $this->getCourseMemberService()->getData()
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
        $this->assertEquals(TimeMachine::time(), $order['pay_time']);
        $this->assertEquals('1', $order['paid_cash_amount']);
        $this->assertEquals('2', $order['expired_refund_days']);
        $this->assertEquals(1517574409, $order['refund_deadline']);
        $this->assertEquals('CourseSet', $order['title']);
    }

    public function testAddUserToClassroom()
    {
        TimeMachine::setMockedTime(1517401609);
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
            'target_type' => 'classroom',
            'target_id' => 12,
            'order_pay_time' => TimeMachine::time(),
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

        $this->getSettingService()->set('refund', array('maxRefundDays' => 2));

        $this->mockBiz('Classroom:ClassroomService', array(
            array('functionName' => 'getClassroom', 'returnValue' => array(
                'id' => 12,
                'title' => 'test classroom',
                'price' => '10.00',
                'middlePicture' => '',
                'maxRate' => 0,
                'smallPicture' => '',
                'largePicture' => '',
                'status' => 'published',
            )),
        ));

        $biz = $this->getBiz();
        $biz['@Marketing:MarketingClassroomMemberService'] = new MockedClassroomMemberServiceImpl($this->getBiz());

        $result = $this->getMarketingClassroomService()->join($postData);

        $this->assertEquals('3', $this->getClassroomMemberService()->getUserId());
        $this->assertEquals(12, $this->getClassroomMemberService()->getClassroomId());
        $order = $this->getClassroomMemberService()->getOrder();

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
            $this->getClassroomMemberService()->getData()
        );

        $this->assertArrayEquals(
            array(
                'is_new' => true,
                'user_id' => '3',
                'code' => 'success',
                'msg' => '把用户,3添加到班级成功,班级ID：12,memberId:12222,订单Id:'.$order['id'],
            ),
            $result,
            array('is_new', 'user_id', 'code', 'msg')
        );

        $this->assertEquals('11000', $order['price_amount']);
        $this->assertEquals('1', $order['pay_amount']);
        $this->assertEquals('classroom', $order['create_extra']['targetType']);
        $this->assertEquals(TimeMachine::time(), $order['pay_time']);
        $this->assertEquals('1', $order['paid_cash_amount']);
        $this->assertEquals('2', $order['expired_refund_days']);
        $this->assertEquals(1517574409, $order['refund_deadline']);
    }

    protected function getMarketingCourseService()
    {
        return $this->createService('Marketing:MarketingCourseService');
    }

    protected function getMarketingClassroomService()
    {
        return $this->createService('Marketing:MarketingClassroomService');
    }

    protected function getUserDao()
    {
        return $this->createDao('User:UserDao');
    }

    protected function getCourseMemberService()
    {
        return $this->createService('Marketing:MarketingCourseMemberService');
    }

    /**
     * @return MockedClassroomMemberServiceImpl
     */
    protected function getClassroomMemberService()
    {
        return $this->createService('Marketing:MarketingClassroomMemberService');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
