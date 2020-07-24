<?php

namespace Tests\Unit\Marketing\Service;

use AppBundle\Common\TimeMachine;
use Biz\BaseTestCase;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Marketing\Service\Impl\MarketingCourseMemberServiceImpl;
use Biz\Marketing\Service\Impl\MarketingCourseServiceImpl;
use Biz\User\Dao\UserDao;
use Codeages\Biz\Order\Service\OrderService;
use Ramsey\Uuid\Uuid;

class MarketingCourseServiceTest extends BaseTestCase
{
    public function testJoin_withExistedUser()
    {
        $course = $this->createCourse();
        $user = $this->getUserDao()->create(
            [
                'nickname' => 'test_user',
                'type' => 'default',
                'email' => 'defaultUser@howzhi.com',
                'password' => '123123',
                'verifiedMobile' => '13675641112',
                'salt' => 'salt1',
                'roles' => ['ROLE_USER'],
                'uuid' => Uuid::uuid4(),
            ]
        );

        $systemUser = $this->getUserDao()->create(
            [
                'nickname' => 'system_user',
                'type' => 'system',
                'email' => 'systemUser@howzhi.com',
                'password' => 'kaifazhe',
                'salt' => 'salt12',
                'roles' => ['ROLE_USER', 'ROLE_TEACHER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN'],
            ]
        );

        TimeMachine::setMockedTime(1517401609);
        $postData = [
            'mobile' => $user['verifiedMobile'],
            'user_id' => $user['id'],
            'nickname' => $user['nickname'],
            'client_ip' => '127.2.3.21',
            'order_id' => 111,
            'order_price_amount' => 11000,
            'order_pay_amount' => 1,
            'activity_id' => 81,
            'activity_name' => '营销活动A',
            'deduct' => [
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
                'snapshot' => [],
                'created_time' => '1511948304',
                'updated_time' => '1511948322',
            ],
            'target_type' => 'course',
            'target_id' => $course['id'],
            'order_pay_time' => TimeMachine::time(),
        ];

        $result = $this->getMarketingCourseService()->join($postData);

        $member = $this->getMarketingCourseMemberService()->getCourseMember($course['id'], $user['id']);
        $orderItems = $this->getOrderService()->searchOrderItems(['user_id' => $user['id']], ['created_time' => 'DESC'], 0, 1);

        $this->assertEquals([
            'is_new' => false,
            'user_id' => $user['id'],
            'code' => 'success',
            'msg' => "把用户,{$user['id']}添加到课程成功,课程ID：{$course['id']},memberId:{$member['id']},订单Id:{$orderItems[0]['order_id']}",
        ], $result);
    }

    public function testJoin_withNotExistedUser()
    {
        $course = $this->createCourse();

        $systemUser = $this->getUserDao()->create(
            [
                'nickname' => 'system_user',
                'type' => 'system',
                'email' => 'systemUser@howzhi.com',
                'password' => 'kaifazhe',
                'salt' => 'salt12',
                'roles' => ['ROLE_USER', 'ROLE_TEACHER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN'],
            ]
        );

        TimeMachine::setMockedTime(1517401609);
        $postData = [
            'mobile' => '13675641112',
            'user_id' => 12,
            'nickname' => 'test_user',
            'client_ip' => '127.2.3.21',
            'order_id' => 111,
            'order_price_amount' => 11000,
            'order_pay_amount' => 1,
            'activity_id' => 81,
            'activity_name' => '营销活动A',
            'password' => '123123',
            'deduct' => [
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
                'snapshot' => [],
                'created_time' => '1511948304',
                'updated_time' => '1511948322',
            ],
            'target_type' => 'course',
            'target_id' => $course['id'],
            'order_pay_time' => TimeMachine::time(),
        ];

        $result = $this->getMarketingCourseService()->join($postData);

        $member = $this->getMarketingCourseMemberService()->getCourseMember($course['id'], 3);
        $orderItems = $this->getOrderService()->searchOrderItems(['user_id' => 3], ['created_time' => 'DESC'], 0, 1);

        $this->assertArrayHasKey('password', $result);
        unset($result['password']);
        $this->assertEquals([
            'is_new' => true,
            'user_id' => '3',
            'code' => 'success',
            'msg' => "把用户,3添加到课程成功,课程ID：{$course['id']},memberId:{$member['id']},订单Id:{$orderItems[0]['order_id']}",
        ], $result);
    }

    protected function createCourse($courseFields = [])
    {
        $courseFields = array_merge([
            'type' => 'normal',
            'title' => 'test course title',
            'about' => 'course about',
            'summary' => 'course summary',
            'price' => '100.00',
        ], $courseFields);
        $courseSet = $this->getCourseSetService()->createCourseSet($courseFields);

        $course = $this->getCourseService()->getCourse($courseSet['defaultCourseId']);
        $course = $this->getCourseService()->updateCourse($course['id'], $courseFields);

        $this->getCourseSetService()->publishCourseSet($courseSet['id']);

        return $this->getCourseService()->getCourse($course['id']);
    }

    /**
     * @return UserDao
     */
    protected function getUserDao()
    {
        return $this->createService('User:UserDao');
    }

    /**
     * @return OrderService
     */
    protected function getOrderService()
    {
        return $this->createService('Order:OrderService');
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
     * @return MarketingCourseServiceImpl
     */
    protected function getMarketingCourseService()
    {
        return $this->createService('Marketing:MarketingCourseService');
    }

    /**
     * @return MarketingCourseMemberServiceImpl
     */
    protected function getMarketingCourseMemberService()
    {
        return $this->createService('Marketing:MarketingCourseMemberService');
    }
}
