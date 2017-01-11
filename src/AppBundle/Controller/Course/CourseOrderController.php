<?php
namespace AppBundle\Controller\Course;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceKernel;
use Topxia\WebBundle\Util\AvatarAlert;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\BaseController;

class CourseOrderController extends BaseController
{
    public function buyAction(Request $request, $id)
    {
        $course = $this->getCourseService()->getCourse($id);
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException();
        }

        $member = $this->getCourseMemberService()->getCourseMember($course['id'], $user['id']);
        if (!empty($member)) {
            return $this->render('course/order/is-member.html.twig', array(
                'course' => $course
            ));
        }

        $remainingStudentNum = $this->getRemainStudentNum($course);
        if ($remainingStudentNum <= 0 && $course['type'] == 'live') {
            return $this->render('course/order/remainless-modal.html.twig', array(
                'course' => $course
            ));
        }

        $userInfo                   = $this->getUserService()->getUserProfile($user['id']);
        $userInfo['approvalStatus'] = $user['approvalStatus'];
        if ($course['approval'] == 1 && ($userInfo['approvalStatus'] != 'approved')) {
            return $this->render('course/order/approve-modal.html.twig', array(
                'course' => $course
            ));
        }

        $userFields = $this->getUserFieldService()->getEnabledFieldsOrderBySeq();

        return $this->render('course/order/buy-modal.html.twig', array(
            'course'           => $course,
            'payments'         => $this->getEnabledPayments(),
            'user'             => $userInfo,
            'avatarAlert'      => AvatarAlert::alertJoinCourse($user),
            'userFields'       => $userFields,
        ));
    }

    public function modifyUserInfoAction(Request $request)
    {
        $formData = $request->request->all();

        $user = $this->getCurrentUser();

        if (empty($user)) {
            return $this->createMessageResponse('error', $this->getServiceKernel()->trans('用户未登录，不能购买。'));
        }

        $course = $this->getCourseService()->getCourse($formData['targetId']);

        if (empty($course)) {
            return $this->createMessageResponse('error', $this->getServiceKernel()->trans('课程不存在，不能购买。'));
        }

        $userInfo = ArrayToolkit::parts($formData, array(
            'truename',
            'mobile',
            'qq',
            'company',
            'weixin',
            'weibo',
            'idcard',
            'gender',
            'job',
            'intField1', 'intField2', 'intField3', 'intField4', 'intField5',
            'floatField1', 'floatField2', 'floatField3', 'floatField4', 'floatField5',
            'dateField1', 'dateField2', 'dateField3', 'dateField4', 'dateField5',
            'varcharField1', 'varcharField2', 'varcharField3', 'varcharField4', 'varcharField5', 'varcharField10', 'varcharField6', 'varcharField7', 'varcharField8', 'varcharField9',
            'textField1', 'textField2', 'textField3', 'textField4', 'textField5', 'textField6', 'textField7', 'textField8', 'textField9', 'textField10'
        ));

        $userInfo = $this->getUserService()->updateUserProfile($user['id'], $userInfo);
        if (isset($formData['email']) && !empty($formData['email'])) {
            $this->getAuthService()->changeEmail($user['id'], null, $formData['email']);
            $this->authenticateUser($this->getUserService()->getUser($user['id']));

            if (!$user['setup']) {
                $this->getUserService()->setupAccount($user['id']);
            }
        }
        
        return $this->redirect($this->generateUrl('order_show', array(
            'targetId'   => $formData['targetId'],
            'targetType' => 'course'
        )));
    }

    public function orderDetailAction(Request $request, $id)
    {
        $order = $this->getOrderService()->getOrder($id);

        if (empty($order)) {
            return $this->createMessageResponse('error', $this->getServiceKernel()->trans('订单不存在!'));
        }

        $this->getCourseService()->tryManageCourse($order["targetId"]);

        return $this->forward('AppBundle:Order:detail', array(
            'id' => $id
        ));
    }

    protected function getEnabledPayments()
    {
        $enableds = array();

        $setting = $this->setting('payment', array());

        if (empty($setting['enabled'])) {
            return $enableds;
        }

        $payment  = $this->container->get('codeages_plugin.dict_twig_extension')->getDict('payment');
        $payNames = array_keys($payment);
        foreach ($payNames as $payName) {
            if (!empty($setting[$payName.'_enabled'])) {
                $enableds[$payName] = array(
                    'type' => empty($setting[$payName.'_type']) ? '' : $setting[$payName.'_type']
                );
            }
        }

        return $enableds;
    }

    protected function getRemainStudentNum($course)
    {
        $remainingStudentNum = $course['maxStudentNum'];

        if ($course['type'] == 'live') {
            if ($course['price'] <= 0) {
                $remainingStudentNum = $course['maxStudentNum'] - $course['studentNum'];
            } else {
                $createdOrdersCount = $this->getOrderService()->countOrders(array(
                    'targetType'             => 'course',
                    'targetId'               => $course['id'],
                    'status'                 => 'created',
                    'createdTimeGreaterThan' => strtotime("-30 minutes")
                ));
                $remainingStudentNum = $course['maxStudentNum'] - $course['studentNum'] - $createdOrdersCount;
            }
        }

        return $remainingStudentNum;
    }

    public function getCourseService()
    {
        return $this->createService('Course:CourseService');
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
}
