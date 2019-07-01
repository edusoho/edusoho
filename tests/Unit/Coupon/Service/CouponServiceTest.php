<?php

namespace Tests\Unit\Coupon\Service;

use Biz\BaseTestCase;
use Biz\Coupon\Dao\CouponDao;
use Biz\Coupon\Service\CouponService;

class CouponServiceTest extends BaseTestCase
{
    public function testGetDeductAmount()
    {
        $coupon = array(
            'type' => 'minus',
            'rate' => 10,
        );

        $deductAmount = $this->getCouponService()->getDeductAmount($coupon, 1);
        $this->assertEquals(10, $deductAmount);

        $coupon['type'] = 'discount';
        $coupon['rate'] = '2';
        $deductAmount = $this->getCouponService()->getDeductAmount($coupon, 10);
        $this->assertEquals(8, $deductAmount);
    }

    /**
     * @expectedException \Biz\Coupon\CouponException
     */
    public function testGetCouponStateByIdWithError()
    {
        $coupon = $this->getCouponDao()->create(array(
            'code' => 'x22232423',
            'type' => 'minus',
            'status' => 'used',
            'rate' => 10,
            'deadline' => time(),
        ));

        $this->getCouponService()->getCouponStateById($coupon['id']);
    }

    /**
     * @expectedException \Biz\Coupon\CouponException
     */
    public function testGetCouponStateByIdWithError3()
    {
        $this->getCouponService()->getCouponStateById(1);
    }

    public function testGetCouponStateById()
    {
        $coupon = $this->getCouponDao()->create(array(
            'code' => 'x22232423',
            'type' => 'minus',
            'status' => 'using',
            'rate' => 10,
            'deadline' => time(),
        ));

        $this->assertInstanceOf('Biz\Coupon\State\UsingCoupon', $this->getCouponService()->getCouponStateById($coupon['id']));
    }

    public function testGenerateDistributionCoupon()
    {
        $notificationService = $this->mockBiz(
            'User:NotificationService',
            array(
                array(
                    'functionName' => 'notify',
                ),
            )
        );

        $result = $this->getCouponService()->generateDistributionCoupon(
            123, 123, 1
        );

        $this->assertEquals('minus', $result['type']);
        $this->assertTrue(0 == strstr($result['code'], 'distributionCoupon'));
        $notificationService->shouldHaveReceived('notify')->times(1);
    }

    public function testGenerateMarketingCoupon()
    {
        $result = $this->getCouponService()->generateMarketingCoupon(123, 123, 1);

        $this->assertEquals('minus', $result['type']);
        $this->assertTrue(0 == strstr($result['code'], 'marketingCoupon'));
    }

    public function testGetCouponTargetByTargetTypeAndTargetIdNull()
    {
        $target = $this->getCouponService()->getCouponTargetByTargetTypeAndTargetId('', '');
        $this->assertEmpty($target);
        $target = $this->getCouponService()->getCouponTargetByTargetTypeAndTargetId('', 233);
        $this->assertEmpty($target);
        $target = $this->getCouponService()->getCouponTargetByTargetTypeAndTargetId('course', '');
        $this->assertEmpty($target);
    }

    public function testgetCouponTargetByTargetTypeAndTargetId()
    {
        $course = array(
            'id' => 233,
            'title' => '测试课程',
        );
        $this->mockBiz(
            'Course:CourseSetService',
            array(
                array(
                    'functionName' => 'getCourseSet',
                    'returnValue' => $course,
                    'withParams' => array(
                        $course['id'],
                    ),
                ),
            )
        );

        $targetCourse = $this->getCouponService()->getCouponTargetByTargetTypeAndTargetId('course', $course['id']);
        $this->assertEquals($course, $targetCourse);

        $vipLevel = array(
            'id' => 122,
            'name' => '测试会员等级',
            'seq' => 122,
        );

        $this->mockBiz(
            'VipPlugin:Vip:LevelService',
            array(
                array(
                    'functionName' => 'getLevel',
                    'returnValue' => $vipLevel,
                    'withParams' => array(
                        $vipLevel['id'],
                    ),
                ),
            )
        );

        $targetVipLevelEmpty = $this->getCouponService()->getCouponTargetByTargetTypeAndTargetId('vip', $vipLevel['id']);
        $this->assertEmpty($targetVipLevelEmpty);

        $this->mockBiz(
            'CloudPlatform:AppService',
            array(
                array(
                    'functionName' => 'getAppByCode',
                    'returnValue' => true,
                    'withParams' => array(
                        'Vip',
                    ),
                ),
            )
        );
        $targetVipLevel = $this->getCouponService()->getCouponTargetByTargetTypeAndTargetId('vip', $vipLevel['id']);
        $this->assertEquals($vipLevel, $targetVipLevel);

        $classroom = array(
            'id' => 23123123,
            'title' => '测试班级',
            'status' => 'published',
        );
        $this->mockBiz(
            'Classroom:ClassroomService',
            array(
                array(
                    'functionName' => 'getClassroom',
                    'returnValue' => $classroom,
                    'withParams' => array(
                        $classroom['id'],
                    ),
                ),
            )
        );
        $targetClassroom = $this->getCouponService()->getCouponTargetByTargetTypeAndTargetId('classroom', $classroom['id']);
        $this->assertEquals($classroom, $targetClassroom);
    }

    public function testAddCoupon()
    {
        $coupon = array(
            'code' => 'x22232423',
            'type' => 'minus',
            'status' => 'using',
            'rate' => 10,
            'deadline' => time(),
        );

        $result = $this->getCouponService()->addCoupon($coupon);

        $getResult = $this->getCouponService()->getCoupon($result['id']);

        $this->assertEquals('x22232423', $result['code']);
        $this->assertEquals('x22232423', $getResult['code']);
    }

    public function testFindCouponsByBatchId()
    {
        $coupon = array(
            'code' => 'x22232423',
            'type' => 'minus',
            'status' => 'using',
            'rate' => 10,
            'deadline' => time(),
            'batchId' => 10,
        );

        $result = $this->getCouponService()->addCoupon($coupon);
        $findRes = $this->getCouponService()->findCouponsByBatchId(10, 0, 100);
        $this->assertEquals(1, count($findRes));
        $firstRes = reset($findRes);
        $this->assertEquals($result['id'], $firstRes['id']);
    }

    public function testFindCouponsByIds()
    {
        $coupon = array(
            'code' => 'x22232423',
            'type' => 'minus',
            'status' => 'using',
            'rate' => 10,
            'deadline' => time(),
            'batchId' => 10,
        );

        $result = $this->getCouponService()->addCoupon($coupon);
        $findRes = $this->getCouponService()->findCouponsByIds(array($result['id']));
        $this->assertEquals(1, count($findRes));
        $firstRes = reset($findRes);
        $this->assertEquals($result['id'], $firstRes['id']);
    }

    public function testSearchCoupons()
    {
        $coupon = array(
            'code' => 'x22232423',
            'type' => 'minus',
            'status' => 'using',
            'rate' => 10,
            'deadline' => time(),
            'batchId' => 10,
        );

        $result = $this->getCouponService()->addCoupon($coupon);
        $findRes = $this->getCouponService()->searchCoupons(array('id' => $result['id']), array('id' => 'DESC'), 0, 100);
        $this->assertEquals(1, count($findRes));
        $firstRes = reset($findRes);
        $this->assertEquals($result['id'], $firstRes['id']);
    }

    public function testSearchCouponsCount()
    {
        $coupon = array(
            'code' => 'x22232423',
            'type' => 'minus',
            'status' => 'using',
            'rate' => 10,
            'deadline' => time(),
            'batchId' => 10,
        );

        $result = $this->getCouponService()->addCoupon($coupon);
        $findResCount = $this->getCouponService()->searchCouponsCount(array('id' => $result['id']));
        $this->assertEquals(1, $findResCount);
    }

    public function testGenerateInviteCouponWithEmptyMode()
    {
        $result = $this->getCouponService()->generateInviteCoupon(1, '');
        $this->assertEquals(array(), $result);
    }

    public function testGenerateInviteCouponWithPay()
    {
        $this->mockBiz('System:SettingService', array(
            array('functionName' => 'get', 'returnValue' => array(
                'invite_code_setting' => 1,
                'promote_user_value' => 1,
                'deadline' => 2,
            ),
            ),
        ));

        $result = $this->getCouponService()->generateInviteCoupon(1, 'pay');

        $this->assertNotEmpty($result);
        $this->assertEquals(1, $result['userId']);
    }

    public function testGenerateInviteCouponWithRegister()
    {
        $this->mockBiz('System:SettingService', array(
            array('functionName' => 'get',
                'returnValue' => array(
                    'invite_code_setting' => 1,
                    'promoted_user_value' => 1,
                    'deadline' => 2,
                ),
            ),
        ));

        $result = $this->getCouponService()->generateInviteCoupon(1, 'register');

        $this->assertNotEmpty($result);
        $this->assertEquals(1, $result['userId']);
    }

    public function testGenerateInviteCouponWithErrorSetting()
    {
        $this->mockBiz('System:SettingService', array(
            array('functionName' => 'get', 'returnValue' => array(
                'invite_code_setting' => 1,
                'promote_user_value' => 0,
                'deadline' => 2,
            ),
            ),
        ));

        $result = $this->getCouponService()->generateInviteCoupon(1, 'pay');

        $this->assertEmpty($result);
    }

    public function testDeleteCouponsByBatch()
    {
        $coupon = array(
            'code' => 'x22232423',
            'type' => 'minus',
            'status' => 'using',
            'rate' => 10,
            'deadline' => time(),
            'batchId' => 10,
        );

        $result = $this->getCouponService()->addCoupon($coupon);
        $findRes = $this->getCouponService()->findCouponsByBatchId(10, 0, 100);
        $this->assertEquals(1, count($findRes));
        $firstRes = reset($findRes);
        $this->assertEquals($result['id'], $firstRes['id']);

        $this->getCouponService()->deleteCouponsByBatch(10);
        $findRes = $this->getCouponService()->findCouponsByBatchId(10, 0, 100);
        $this->assertEquals(0, count($findRes));
    }

    public function testCheckCouponUseableWithEmptyCoupon()
    {
        $result = $this->getCouponService()->checkCouponUseable('123456', 'minus', '1', '100');
        $this->assertEquals(array(
            'useable' => 'no',
            'message' => '该优惠券不存在',
        ), $result);
    }

    public function testCheckCouponUseableWithUsedeCoupon()
    {
        $this->getCouponDao()->create(array(
            'code' => 'x22232423',
            'type' => 'minus',
            'status' => 'used',
            'rate' => 10,
            'deadline' => time(),
        ));

        $result = $this->getCouponService()->checkCouponUseable('x22232423', 'minus', '1', '100');
        $this->assertEquals(array(
            'useable' => 'no',
            'message' => sprintf('优惠券%s已经被使用', 'x22232423'),
        ), $result);
    }

    public function testCheckCouponUseableWithWrongUserId()
    {
        $this->getCouponDao()->create(array(
            'code' => 'x22232423',
            'type' => 'minus',
            'status' => 'unused',
            'rate' => 10,
            'deadline' => time(),
            'userId' => $this->getCurrentUser()->getId() + 1,
        ));

        $result = $this->getCouponService()->checkCouponUseable('x22232423', 'minus', '1', '100');
        $this->assertEquals(array(
            'useable' => 'no',
            'message' => sprintf('优惠券%s已经被其他人领取使用', 'x22232423'),
        ), $result);
    }

    public function testCheckCouponUseableWithErrorDeadline()
    {
        $this->getCouponDao()->create(array(
            'code' => 'x22232423',
            'type' => 'minus',
            'status' => 'unused',
            'rate' => 10,
            'deadline' => time() - 86400 * 2,
            'userId' => $this->getCurrentUser()->getId(),
        ));

        $result = $this->getCouponService()->checkCouponUseable('x22232423', 'minus', '1', '100');
        $this->assertEquals(array(
            'useable' => 'no',
            'message' => sprintf('优惠券%s已过期', 'x22232423'),
        ), $result);
    }

    public function testCheckCouponUseableWithNotAllAndFullDiscount()
    {
        $this->getCouponDao()->create(array(
            'code' => 'x22232423',
            'type' => 'minus',
            'status' => 'unused',
            'rate' => 10,
            'deadline' => time(),
            'userId' => $this->getCurrentUser()->getId(),
        ));

        $result = $this->getCouponService()->checkCouponUseable('x22232423', 'fullDiscount', '1', '100');
        $this->assertEquals(array(
            'useable' => 'no',
            'message' => '',
        ), $result);
    }

    public function testCheckCouponUseableWithMinus()
    {
        $this->getCouponDao()->create(array(
            'code' => 'x22232423',
            'type' => 'minus',
            'status' => 'unused',
            'targetType' => 'all',
            'rate' => 10,
            'deadline' => time(),
            'userId' => $this->getCurrentUser()->getId(),
        ));

        $result = $this->getCouponService()->checkCouponUseable('x22232423', 'all', '0', '100');

        $this->assertEquals(90, $result['afterAmount']);
    }

    public function testGetCouponByIds()
    {
        $coupon = array(
            'code' => 'x22232423',
            'type' => 'minus',
            'status' => 'using',
            'rate' => 10,
            'deadline' => time(),
            'batchId' => 10,
        );

        $result = $this->getCouponService()->addCoupon($coupon);
        $findRes = $this->getCouponService()->getCouponsByIds(array($result['id']));
        $this->assertEquals(1, count($findRes));
        $firstRes = reset($findRes);
        $this->assertEquals($result['id'], $firstRes['id']);
    }

    public function testUpdateCoupon()
    {
        $coupon = array(
            'code' => 'x22232423',
            'type' => 'minus',
            'status' => 'using',
            'rate' => 10,
            'deadline' => time(),
            'batchId' => 10,
        );
        $result = $this->getCouponService()->addCoupon($coupon);
        $findRes = $this->getCouponService()->updateCoupon($result['id'], array('status' => 'used'));
        $this->assertEquals('used', $findRes['status']);
    }

    public function testCheckCouponWithUsedCoupon()
    {
        $this->mockBiz('Course:CourseService', array(
            array('functionName' => 'getCourse', 'returnValue' => array('id' => 1, 'courseSetId' => 1)),
        ));
        $coupon = array(
            'code' => 'x22232423',
            'type' => 'minus',
            'status' => 'used',
            'rate' => 10,
            'deadline' => time(),
            'batchId' => 10,
        );
        $this->getCouponService()->addCoupon($coupon);

        $result = $this->getCouponService()->checkCoupon('x22232423', 1, 'course');
        $this->assertEquals(array(
            'useable' => 'no',
            'message' => sprintf('优惠券%s已经被使用', 'x22232423'),
        ), $result);
    }

    public function testCheckCouponWithWrongUser()
    {
        $this->mockBiz('Course:CourseService', array(
            array('functionName' => 'getCourse', 'returnValue' => array('id' => 1, 'courseSetId' => 1)),
        ));
        $coupon = array(
            'code' => 'x22232423',
            'type' => 'minus',
            'status' => 'unused',
            'rate' => 10,
            'deadline' => time(),
            'userId' => $this->getCurrentUser()->getId() + 1,
            'batchId' => 10,
        );
        $this->getCouponService()->addCoupon($coupon);

        $result = $this->getCouponService()->checkCoupon('x22232423', 1, 'course');
        $this->assertEquals(array(
            'useable' => 'no',
            'message' => sprintf('优惠券%s已经被其他人领取使用', 'x22232423'),
        ), $result);
    }

    public function testCheckCouponWithWrongDeadline()
    {
        $this->mockBiz('Course:CourseService', array(
            array('functionName' => 'getCourse', 'returnValue' => array('id' => 1, 'courseSetId' => 1)),
        ));
        $coupon = array(
            'code' => 'x22232423',
            'type' => 'minus',
            'status' => 'unused',
            'rate' => 10,
            'deadline' => time() - 86400 * 2,
            'userId' => $this->getCurrentUser()->getId(),
            'batchId' => 10,
        );
        $this->getCouponService()->addCoupon($coupon);

        $result = $this->getCouponService()->checkCoupon('x22232423', 1, 'course');
        $this->assertEquals(array(
            'useable' => 'no',
            'message' => sprintf('优惠券%s已过期', 'x22232423'),
        ), $result);
    }

    public function testCheckCouponWithWrongUnValid()
    {
        $this->mockBiz('Course:CourseService', array(
            array('functionName' => 'getCourse', 'returnValue' => array('id' => 1, 'courseSetId' => 1)),
        ));
        $coupon = array(
            'code' => 'x22232423',
            'type' => 'minus',
            'status' => 'unused',
            'rate' => 10,
            'deadline' => time(),
            'userId' => $this->getCurrentUser()->getId(),
            'batchId' => 10,
        );
        $this->getCouponService()->addCoupon($coupon);

        $result = $this->getCouponService()->checkCoupon('x22232423', 1, 'course');
        $this->assertEquals(array(
            'useable' => 'no',
            'message' => '该优惠券不能被该商品使用',
        ), $result);
    }

    public function testCheckCouponWith()
    {
        $this->mockBiz('Course:CourseService', array(
            array('functionName' => 'getCourse', 'returnValue' => array('id' => 1, 'courseSetId' => 1)),
        ));
        $coupon = array(
            'code' => 'x22232423',
            'type' => 'minus',
            'status' => 'unused',
            'targetType' => 'all',
            'rate' => 10,
            'deadline' => time(),
            'userId' => $this->getCurrentUser()->getId(),
            'batchId' => 10,
        );
        $this->getCouponService()->addCoupon($coupon);

        $result = $this->getCouponService()->checkCoupon('x22232423', 1, 'course1');
        $this->assertEquals('x22232423', $result['code']);
    }

    /**
     * @return CouponDao
     */
    private function getCouponDao()
    {
        return $this->createDao('Coupon:CouponDao');
    }

    /**
     * @return CouponService
     */
    private function getCouponService()
    {
        return $this->createService('Coupon:CouponService');
    }
}
