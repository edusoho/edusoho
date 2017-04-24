<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ResourceNotFoundException;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\MemberService;
use Biz\Order\Service\OrderService;

class CourseMember extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request, $courseId)
    {
        $conditions = $request->query->all();
        $conditions['courseId'] = $courseId;
        $conditions['locked'] = 0;

        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $members = $this->service('Course:MemberService')->searchMembers(
            $conditions,
            array('createdTime' => 'DESC'),
            $offset,
            $limit
        );

        $total = $this->service('Course:MemberService')->countMembers($conditions);

        $this->getOCUtil()->multiple($members, array('userId'));

        return $this->makePagingObject($members, $total, $offset, $limit);
    }

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $courseId, $userId)
    {
        $courseMember = $this->getMemberService()->getCourseMember($courseId, $userId);
        $this->getOCUtil()->single($courseMember, array('userId'));
        return $courseMember;
    }

    public function add(ApiRequest $request, $courseId)
    {
        $course = $this->service('Course:CourseService')->getCourse($courseId);

        if (!$course) {
            throw new ResourceNotFoundException('教学计划不存在');
        }

        $member = $this->getMemberService()->getCourseMember($courseId, $this->getCurrentUser()->getId());

        if (!$member) {
            $member = $this->tryJoin($course);
        }

        if ($member) {
            $this->getOCUtil()->single($member, array('userId'));
            return $member;
        }

        return null;
    }

    private function tryJoin($course)
    {
        $member = $this->freeJoin($course);
        if ($member) {
            return $member;
        }

        return $this->vipJoin($course);
    }

    private function freeJoin($course)
    {
        if ($course['isFree'] == 0 || $course['price'] == 0) {
            return null;
        }

        $member = $this->getMemberService()->becomeStudent($course['id'], $this->getCurrentUser()->id);

        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);

        $systemOrder = array(
            'userId' => $this->getCurrentUser()->id,
            'title' => "购买课程《{$courseSet['title']}》- {$course['title']}",
            'targetType' => OrderService::TARGETTYPE_COURSE,
            'targetId' => $course['id'],
            'amount' => 0,
            'totalPrice' => $course['price'],
            'snPrefix' => OrderService::SNPREFIX_C,
            'payment' => '',
        );

        $order = $this->getOrderService()->createSystemOrder($systemOrder);
        $this->getMemberService()->updateMember($member['id'], array(
            'orderId' => $order['id']
        ));

        return $member;
    }

    private function vipJoin($course)
    {
        if (!$this->isPluginInstalled('vip')) {
            return null;
        }

        list($success, $message) = $this->service('VipPlugin:Vip:VipFacadeService')->joinCourse($course['id']);
        if ($success) {
            return $this->getMemberService()->getCourseMember($course['id'], $this->getCurrentUser()->getId());
        } else {
            return null;
        }
    }

    /**
     * @return MemberService
     */
    private function getMemberService()
    {
        return $this->service('Course:MemberService');
    }

    /**
     * @return OrderService
     */
    private function getOrderService()
    {
        return $this->service('Order:OrderService');
    }

    /**
     * @return CourseSetService
     */
    private function getCourseSetService()
    {
        return $this->service('Course:CourseSetService');
    }
}