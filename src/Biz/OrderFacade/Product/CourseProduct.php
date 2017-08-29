<?php

namespace Biz\OrderFacade\Product;

use Biz\Accessor\AccessorInterface;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\MemberService;
use Codeages\Biz\Framework\Order\Callback\PaidCallback;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Biz\OrderFacade\Product\Refund\Refund;
class CourseProduct extends Product implements PaidCallback, Refund
{
    const TYPE = 'course';

    public $showTemplate = 'order/show/course-item.html.twig';

    public $targetType = self::TYPE;

    public $courseSet;

    public function init(array $params)
    {
        $this->targetId = $params['targetId'];
        $course = $this->getCourseService()->getCourse($this->targetId);
        $this->backUrl = array('routing' => 'course_show', 'params' => array('id' => $course['id']));
        $this->successUrl = array('my_course_show', array('id' => $this->targetId));
        $this->title = $course['title'];
        $this->courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        $this->price = $course['price'];
    }

    public function validate()
    {
        $access = $this->getCourseService()->canJoinCourse($this->targetId);

        if ($access['code'] !== AccessorInterface::SUCCESS) {
            throw new InvalidArgumentException($access['msg']);
        }
    }

    public function paidCallback($orderItem)
    {
        $info = array(
            'orderId' => $orderItem['order_id'],
            'remark' => '',
        );

        if (!$this->getCourseMemberService()->isCourseStudent($orderItem['target_id'], $orderItem['user_id'])) {
            $this->getCourseMemberService()->becomeStudent($orderItem['target_id'], $orderItem['user_id'], $info);
        }

        return PaidCallback::SUCCESS;
    }

    public function lockMember()
    {
        $user = $this->biz['user'];
        $this->getCourseMemberService()->lockStudent($this->targetId, $user->getId());
    }

    public function removeMember()
    {
        $user = $this->biz['user'];
        $this->getCourseMemberService()->removeStudent($this->targetId, $user->getId());
    }

    /**
     * @return MemberService
     */
    private function getCourseMemberService()
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
