<?php

namespace AppBundle\Controller\Course;

use AppBundle\Controller\BaseController;
use AppBundle\Util\AvatarAlert;
use Biz\Course\Service\CourseSetService;
use Biz\MultiClass\Service\MultiClassService;
use Biz\User\UserException;
use Symfony\Component\HttpFoundation\Request;
use VipPlugin\Biz\Marketing\VipRightSupplier\CourseVipRightSupplier;

class CourseOrderController extends BaseController
{
    public function buyAction(Request $request, $id)
    {
        $course = $this->getCourseService()->getCourse($id);
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        $member = $this->getCourseMemberService()->getCourseMember($course['id'], $user['id']);
        if (!empty($member)) {
            return $this->render(
                'course/order/is-member.html.twig',
                [
                    'course' => $course,
                ]
            );
        }

        $vipJoinEnabled = false;
        if ($this->isPluginInstalled('Vip') && $this->setting('vip.enabled')) {
            $vipJoinEnabled = 'ok' === $this->getVipService()->checkUserVipRight($user['id'], CourseVipRightSupplier::CODE, $course['id']);
        }

        $paymentSetting = $this->setting('payment');
        if ($course['price'] > 0 && !$paymentSetting['enabled'] && !$vipJoinEnabled) {
            return $this->render(
                'buy-flow/payments-disabled-modal.html.twig',
                [
                    'course' => $course,
                ]
            );
        }

        $userInfo = $this->getUserService()->getUserProfile($user['id']);
        $userInfo['approvalStatus'] = $user['approvalStatus'];
        if (1 == $course['approval'] && ('approved' != $userInfo['approvalStatus'])) {
            return $this->render(
                'course/order/approve-modal.html.twig',
                [
                    'course' => $course,
                ]
            );
        }

        $remainingStudentNum = $this->getRemainStudentNum($course);
        if ($remainingStudentNum <= 0 && 'live' == $course['type']) {
            return $this->render(
                'course/order/remainless-modal.html.twig',
                [
                    'course' => $course,
                ]
            );
        }

        $multiClass = $this->getMultiClassService()->getMultiClassByCourseId($course['id']);
        if (!empty($multiClass['maxStudentNum'])) {
            $remainingStudentNum = $this->getMultiClassRemainStudentNum($multiClass, $course);
            if ($remainingStudentNum <= 0) {
                return $this->render(
                    'course/order/remainless-modal.html.twig',
                    [
                        'course' => $course,
                    ]
                );
            }
        }

        if (AvatarAlert::alertJoinCourse($user)) {
            return $this->render(
                'course/order/avatar-alert-modal.html.twig',
                [
                    'course' => $course,
                ]
            );
        }

        $userFields = $this->getUserFieldService()->getEnabledFieldsOrderBySeq();
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);

        return $this->render(
            'course/order/buy-modal.html.twig',
            [
                'course' => $course,
                'courseId' => $course['id'],
                'courseSet' => $courseSet,
                'user' => $userInfo,
                'userFields' => $userFields,
            ]
        );
    }

    public function orderDetailAction(Request $request, $id)
    {
        $order = $this->getOrderService()->getOrder($id);

        if (empty($order)) {
            return $this->createMessageResponse('error', $this->getServiceKernel()->trans('订单不存在!'));
        }

        $this->getCourseService()->tryManageCourse($order['targetId']);

        return $this->forward(
            'AppBundle:Order:detail',
            [
                'id' => $id,
            ]
        );
    }

    protected function getRemainStudentNum($course)
    {
        $remainingStudentNum = $course['maxStudentNum'];

        if ('live' == $course['type']) {
            if ($course['price'] <= 0) {
                $remainingStudentNum = $course['maxStudentNum'] - $course['studentNum'];
            } else {
                $createdOrdersCount = $this->getOrderService()->countOrders(
                    [
                        'targetType' => 'course',
                        'targetId' => $course['id'],
                        'status' => 'created',
                        'createdTimeGreaterThan' => strtotime('-30 minutes'),
                    ]
                );
                $remainingStudentNum = $course['maxStudentNum'] - $course['studentNum'] - $createdOrdersCount;
            }
        }

        return $remainingStudentNum;
    }

    protected function getMultiClassRemainStudentNum($multiClass, $course)
    {
        if ($course['price'] <= 0) {
            $remainingStudentNum = $multiClass['maxStudentNum'] - $course['studentNum'];
        } else {
            $createdOrdersCount = $this->getOrderService()->countOrders(
                [
                    'targetType' => 'course',
                    'targetId' => $course['id'],
                    'status' => 'created',
                    'createdTimeGreaterThan' => strtotime('-30 minutes'),
                ]
            );
            $remainingStudentNum = $multiClass['maxStudentNum'] - $course['studentNum'] - $createdOrdersCount;
        }

        return $remainingStudentNum;
    }

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

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }

    protected function getAuthService()
    {
        return $this->createService('User:AuthService');
    }

    protected function getUserFieldService()
    {
        return $this->createService('User:UserFieldService');
    }

    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    /**
     * @return VipService
     */
    protected function getVipService()
    {
        return $this->createService('VipPlugin:Vip:VipService');
    }

    /**
     * @return MultiClassService
     */
    protected function getMultiClassService()
    {
        return $this->createService('MultiClass:MultiClassService');
    }
}
