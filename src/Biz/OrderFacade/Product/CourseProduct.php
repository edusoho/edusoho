<?php

namespace Biz\OrderFacade\Product;

use Biz\Accessor\AccessorInterface;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\MemberService;
use Biz\OrderFacade\Exception\OrderPayCheckException;
use Biz\Course\Util\CourseTitleUtils;
use Codeages\Biz\Order\Status\OrderStatusCallback;

class CourseProduct extends Product implements OrderStatusCallback
{
    const TYPE = 'course';

    public $showTemplate = 'order/show/course-item.html.twig';

    public $targetType = self::TYPE;

    public $courseSet;

    /**
     * 课程展示价格
     *
     * @var float
     */
    public $price;

    public function init(array $params)
    {
        $this->targetId = $params['targetId'];
        $course = $this->getCourseService()->getCourse($this->targetId);
        $this->backUrl = array('routing' => 'course_show', 'params' => array('id' => $course['id']));
        $this->successUrl = array('my_course_show', array('id' => $this->targetId));
        $this->courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        $this->title = CourseTitleUtils::getDisplayedTitle($course);
        $this->price = $course['price'];
        $this->originPrice = $course['originPrice'];
        $this->maxRate = $course['maxRate'];
        $this->cover = $this->courseSet['cover'];
    }

    public function validate()
    {
        $access = $this->getCourseService()->canJoinCourse($this->targetId);

        $course = $this->getCourseService()->getCourse($this->targetId);

        if (!$course['buyable']) {
            throw new OrderPayCheckException('order.pay_check_msg.unpurchasable_product', Product::PRODUCT_VALIDATE_FAIL);
        }

        if (AccessorInterface::SUCCESS !== $access['code']) {
            throw new OrderPayCheckException($access['msg'], Product::PRODUCT_VALIDATE_FAIL);
        }
    }

    public function onPaid($orderItem)
    {
        $targetName = '课程';
        $this->smsCallback($orderItem, $targetName);

        $order = $this->getOrderService()->getOrder($orderItem['order_id']);
        $info = array(
            'orderId' => $order['id'],
            'remark' => $order['created_reason'],
        );

        try {
            if (!$this->getCourseMemberService()->isCourseStudent($orderItem['target_id'], $orderItem['user_id'])) {
                $member = $this->getCourseMemberService()->becomeStudent($orderItem['target_id'], $orderItem['user_id'], $info);
            }

            if (isset($member)) {
                $course = $this->getCourseService()->getCourse($orderItem['target_id']);
                $this->getLogService()->info('course', 'join_course', "加入教学计划《{$course['title']}》", array('userId' => $orderItem['user_id'], 'id' => $course['id'], 'title' => $course['title'], 'courseSetTitle' => $course['courseSetTitle']));
            }

            return OrderStatusCallback::SUCCESS;
        } catch (\Exception $e) {
            $this->getLogService()->error('order', 'course_callback', 'order.course_callback.fail',
                array('error' => $e->getMessage(), 'context' => $orderItem));

            return false;
        }
    }

    public function onOrderRefundAuditing($orderRefundItem)
    {
        $orderItem = $orderRefundItem['order_item'];
        $this->getCourseMemberService()->lockStudent($orderItem['target_id'], $orderItem['user_id']);
    }

    public function onOrderRefundCancel($orderRefundItem)
    {
        $orderItem = $orderRefundItem['order_item'];
        $this->getCourseMemberService()->unlockStudent($orderItem['target_id'], $orderItem['user_id']);
    }

    public function onOrderRefundRefunded($orderRefundItem)
    {
        $orderItem = $orderRefundItem['order_item'];

        $member = $this->getCourseMemberService()->getCourseMember($orderItem['target_id'], $orderItem['user_id']);
        if (!empty($member)) {
            $this->getCourseMemberService()->removeStudent($orderItem['target_id'], $orderItem['user_id']);
        }

        $this->updateMemberRecordByRefundItem($orderItem);
    }

    public function onOrderRefundRefused($orderRefundItem)
    {
        $orderItem = $orderRefundItem['order_item'];
        $this->getCourseMemberService()->unlockStudent($orderItem['target_id'], $orderItem['user_id']);
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
