<?php
namespace Topxia\Service\Course\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\Course\CourseMemberService;

class CourseMemberServiceImpl extends BaseService implements CourseMemberService
{
    public function becomeStudentAndCreateOrder($userId, $courseId, $data)
    {
        if (!ArrayToolkit::requireds($data, array("price", "remark"))) {
            throw $this->createServiceException($this->getKernel()->trans('参数不对！'));
        }

        $user = $this->getUserService()->getUser($userId);

        if (empty($user)) {
            throw $this->createNotFoundException($this->getKernel()->trans('用户%userNickName%不存在', array('%userNickName%' => $user['nickname'])));
        }

        $course = $this->getCourseService()->getCourse($courseId);

        if (empty($course)) {
            throw $this->createNotFoundException($this->getKernel()->trans('课程%courseTitle%不存在', array('%courseTitle%' => $course['title'])));
        }

        if ($this->getCourseService()->isCourseStudent($course['id'], $user['id'])) {
            throw $this->createNotFoundException($this->getKernel()->trans('用户已经是学员，不能添加！'));
        }

        $orderTitle = $this->getKernel()->trans('购买课程《%courseTitle%》', array('%courseTitle%' => $course['title']));

        if (isset($data["isAdminAdded"]) && $data["isAdminAdded"] == 1) {
            $orderTitle = $orderTitle.'('.$this->getKernel()->trans('管理员添加').')';
            $payment    = 'outside';
        } else {
            $payment = 'none';
        }

        if (empty($data['price'])) {
            $data['price'] = 0;
        }

        $order = $this->getOrderService()->createOrder(array(
            'userId'     => $user['id'],
            'title'      => $orderTitle,
            'targetType' => 'course',
            'targetId'   => $course['id'],
            'amount'     => $data['price'],
            'totalPrice' => $data['price'],
            'payment'    => $payment,
            'snPrefix'   => 'C'
        ));

        $this->getOrderService()->payOrder(array(
            'sn'       => $order['sn'],
            'status'   => 'success',
            'amount'   => $order['amount'],
            'paidTime' => time()
        ));

        $info = array(
            'orderId'         => $order['id'],
            'note'            => $data['remark'],
            'becomeUseMember' => isset($data['becomeUseMember']) ? $data['becomeUseMember'] : false
        );

        $this->getCourseService()->becomeStudent($order['targetId'], $order['userId'], $info);

        $member = $this->getCourseService()->getCourseMember($course['id'], $user['id']);

        if (isset($data["isAdminAdded"]) && $data["isAdminAdded"] == 1) {
            $this->getNotificationService()->notify($member['userId'], 'student-create', array(
                'courseId'    => $course['id'],
                'courseTitle' => $course['title']
            ));
        }

        $this->getLogService()->info('course', 'add_student', "课程《{$course['title']}》(#{$course['id']})，添加学员{$user['nickname']}(#{$user['id']})，备注：{$data['remark']}");

        return array($course, $member, $order);
    }

    protected function getUserService()
    {
        return $this->createService('User.UserService');
    }

    protected function getOrderService()
    {
        return $this->createService('Order.OrderService');
    }

    protected function getLogService()
    {
        return $this->createService('System.LogService');
    }

    protected function getNotificationService()
    {
        return $this->createService('User.NotificationService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course.CourseService');
    }
}
