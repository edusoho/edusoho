<?php

namespace Biz\OrderFacade\Product;

use Biz\Accessor\AccessorInterface;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\MemberService;
use Codeages\Biz\Framework\Order\Status\OrderStatusCallback;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;

class CourseProduct extends Product implements Owner, OrderStatusCallback
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
        $this->title = $this->courseSet['title'].'-'.$course['title'];
        $this->price = $course['price'];
        $this->originPrice = $course['originPrice'];
        $this->maxRate = $course['maxRate'];
    }

    public function validate()
    {
        $access = $this->getCourseService()->canJoinCourse($this->targetId);

        if ($access['code'] !== AccessorInterface::SUCCESS) {
            throw new InvalidArgumentException($access['msg']);
        }
    }

    public function onPaid($orderItem)
    {
        $this->smsCallback($orderItem);

        $order = $this->getOrderService()->getOrder($orderItem['order_id']);
        $info = array(
            'orderId' => $order['id'],
            'note' => $order['created_reason'],
        );

        try {
            if (!$this->getCourseMemberService()->isCourseStudent($orderItem['target_id'], $orderItem['user_id'])) {
                $this->getCourseMemberService()->becomeStudent($orderItem['target_id'], $orderItem['user_id'], $info);
            }

            return OrderStatusCallback::SUCCESS;
        } catch (\Exception $e) {
            $this->getLogService()->error('order', 'course_callback', 'order.course_callback.fail',
                array('error' => $e->getMessage(), 'context' => $orderItem));

            return false;
        }
    }

    public function onApplyRefund()
    {
        $user = $this->biz['user'];
        $this->getCourseMemberService()->lockStudent($this->targetId, $user->getId());
    }

    public function onCancelRefund()
    {
        $user = $this->biz['user'];
        $this->getCourseMemberService()->unlockStudent($this->targetId, $user->getId());
    }

    public function onAdoptRefund()
    {
        $this->getCourseMemberService()->removeStudent($this->targetId, $userId);
    }

    public function onRefuseRefund($order)
    {
        $this->getCourseMemberService()->unlockStudent($this->targetId, $order['created_user_id']);
    }

    public function exitOwner($data)
    {
        $user = $this->biz['user'];
        $this->getCourseMemberService()->removeStudent(
            $this->targetId,
            $user->getId(),
            array('reason' => $data['reason']['note'])
        );
    }

    public function getOwner($userId)
    {
        return $this->getCourseMemberService()->getCourseMember($this->targetId, $userId);
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
