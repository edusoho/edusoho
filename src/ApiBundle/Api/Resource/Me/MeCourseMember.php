<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ApiException;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\Order\OrderRefundProcessor\OrderRefundProcessorFactory;
use Biz\Order\Service\OrderService;
use ApiBundle\Api\Annotation\ApiFilter;

class MeCourseMember extends AbstractResource
{
    /**
     * @ApiFilter(class="ApiBundle\Api\Resource\Course\CourseMemberFilter", mode="public"))
     */
    public function get(ApiRequest $request, $courseId)
    {
        $courseMember = $this->getCourseMemberService()->getCourseMember($courseId, $this->getCurrentUser()->getId());
        $this->getOCUtil()->single($courseMember, array('userId'));

        $courseMember['access'] = $this->getCourseService()->canLearnCourse($courseId);

        return $courseMember;
    }

    public function remove(ApiRequest $request, $courseId)
    {
        $reason = $request->request->get('reason', '从App退出课程');
        $processor = OrderRefundProcessorFactory::create('course');

        $user = $this->getCurrentUser();
        $member = $processor->getTargetMember($courseId, $user['id']);

        if (empty($member) || empty($member['orderId'])) {
            throw new ApiException('您不是学员或尚未购买，不能退学。');
        }

        $order = $this->getOrderService()->getOrder($member['orderId']);

        if (empty($order)) {
            throw new ApiException();
        }

        if ($order['targetType'] == 'groupSell') {
            throw new ApiException('组合购买课程不能退出。');
        }

        $processor->applyRefundOrder($member['orderId'], 0, array('note' => $reason), null);

        return array('success' => true);
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return OrderService
     */
    private function getOrderService()
    {
        return $this->service('Order:OrderService');
    }

    /**
     * @return MemberService
     */
    private function getCourseMemberService()
    {
        return $this->service('Course:MemberService');
    }
}