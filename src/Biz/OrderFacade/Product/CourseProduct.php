<?php

namespace Biz\OrderFacade\Product;

use Biz\Accessor\AccessorInterface;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\MemberService;
use Biz\OrderFacade\Exception\OrderPayCheckException;
use Codeages\Biz\Order\Status\OrderStatusCallback;

class CourseProduct extends BaseGoodsProduct implements OrderStatusCallback
{
    const TYPE = 'course';

    public $showTemplate = 'order/show/course-item.html.twig';

    public $targetType = self::TYPE;

    /**
     * 课程展示价格
     *
     * @var float
     */
    public $price;

    public function validate()
    {
        $access = $this->getCourseService()->canJoinCourse($this->goodsSpecs['targetId']);

        $course = $this->getCourseService()->getCourse($this->goodsSpecs['targetId']);

        if (!$course['buyable']) {
            throw OrderPayCheckException::UNPURCHASABLE_PRODUCT();
        }

        if (AccessorInterface::SUCCESS !== $access['code']) {
            throw OrderPayCheckException::UNPURCHASABLE_PRODUCT();
        }
    }

    public function onPaid($orderItem)
    {
        $targetName = '课程';
        $this->smsCallback($orderItem, $targetName);

        $order = $this->getOrderService()->getOrder($orderItem['order_id']);
        $info = [
            'orderId' => $order['id'],
            'remark' => $order['created_reason'],
        ];

        $course = $this->getCourseByGoodsSpecsId($orderItem['target_id']);

        try {
            if (!$this->getCourseMemberService()->isCourseStudent($course['id'], $orderItem['user_id'])) {
                $member = $this->getCourseMemberService()->becomeStudent($course['id'], $orderItem['user_id'], $info);
            }

            if (isset($member)) {
                $this->getLogService()->info('course', 'join_course', "加入教学计划《{$course['title']}》", ['userId' => $orderItem['user_id'], 'courseId' => $course['id'], 'title' => ($course['title']) ? $course['title'] : $course['courseSetTitle']]);
            }

            return OrderStatusCallback::SUCCESS;
        } catch (\Exception $e) {
            $this->getLogService()->error('order', 'course_callback', 'order.course_callback.fail',
                ['error' => $e->getMessage(), 'context' => $orderItem]);

            return false;
        }
    }

    public function onOrderRefundAuditing($orderRefundItem)
    {
        $orderItem = $orderRefundItem['order_item'];
        $course = $this->getCourseByGoodsSpecsId($orderItem['target_id']);

        $this->getCourseMemberService()->lockStudent($course['id'], $orderItem['user_id']);
    }

    public function onOrderRefundCancel($orderRefundItem)
    {
        $orderItem = $orderRefundItem['order_item'];
        $course = $this->getCourseByGoodsSpecsId($orderItem['target_id']);

        $this->getCourseMemberService()->unlockStudent($course['id'], $orderItem['user_id']);
    }

    public function onOrderRefundRefunded($orderRefundItem)
    {
        $orderItem = $orderRefundItem['order_item'];
        $course = $this->getCourseByGoodsSpecsId($orderItem['target_id']);

        $member = $this->getCourseMemberService()->getCourseMember($course['id'], $orderItem['user_id']);
        if (!empty($member)) {
            $this->getCourseMemberService()->removeStudent($course['id'], $orderItem['user_id']);
        }

        $this->updateMemberRecordByRefundItem($orderItem);
    }

    public function onOrderRefundRefused($orderRefundItem)
    {
        $orderItem = $orderRefundItem['order_item'];
        $course = $this->getCourseByGoodsSpecsId($orderItem['target_id']);

        if (!empty($course)) {
            $this->getCourseMemberService()->unlockStudent($course['id'], $orderItem['user_id']);
        }
    }

    protected function getCourseByGoodsSpecsId($id)
    {
        $goodsSpecs = $this->getGoodsService()->getGoodsSpecs($id);

        return $this->getCourseService()->getCourse($goodsSpecs['targetId']);
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->biz->service('Course:MemberService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->biz->service('Course:CourseSetService');
    }
}
