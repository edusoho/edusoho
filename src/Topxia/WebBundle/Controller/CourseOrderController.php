<?php
namespace Topxia\WebBundle\Controller;

use Topxia\Common\ArrayToolkit;
use Topxia\WebBundle\Util\AvatarAlert;
use Symfony\Component\HttpFoundation\Request;

class CourseOrderController extends OrderController
{
    public $courseId = 0;

    public function buyAction(Request $request, $id)
    {
        $course = $this->getCourseService()->getCourse($id);

        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException();
        }

        $remainingStudentNum = $this->getRemainStudentNum($course);

        $previewAs = $request->query->get('previewAs');

        $member = $user ? $this->getCourseService()->getCourseMember($course['id'], $user['id']) : null;
        $member = $this->previewAsMember($previewAs, $member, $course);

        $courseSetting = $this->getSettingService()->get('course', array());

        $userInfo                   = $this->getUserService()->getUserProfile($user['id']);

        $userInfo['approvalStatus'] = $user['approvalStatus'];

        $account = $this->getCashAccountService()->getAccountByUserId($user['id'], true);

        if (empty($account)) {
            $this->getCashAccountService()->createAccount($user['id']);
        }

        if (isset($account['cash'])) {
            $account['cash'] = intval($account['cash']);
        }

        $amount = $this->getOrderService()->analysisAmount(array('userId' => $user->id, 'status' => 'paid'));
        $amount += $this->getCashOrdersService()->analysisAmount(array('userId' => $user->id, 'status' => 'paid'));

        $course = $this->getCourseService()->getCourse($id);

        $userFields = $this->getUserFieldService()->getAllFieldsOrderBySeqAndEnabled();

        if ($course['approval'] == 1 && ($userInfo['approvalStatus'] != 'approved')) {
            return $this->render('TopxiaWebBundle:CourseOrder:approve-modal.html.twig', array(
                'course' => $course
            ));
        }

        if ($remainingStudentNum <= 0 && $course['type'] == 'live') {
            return $this->render('TopxiaWebBundle:CourseOrder:remainless-modal.html.twig', array(
                'course' => $course
            ));
        }

        //判断用户是否为VIP
        $vipStatus = $courseVip = null;

        if ($this->isPluginInstalled('Vip') && $this->setting('vip.enabled')) {
            $courseVip = $course['vipLevelId'] > 0 ? $this->getLevelService()->getLevel($course['vipLevelId']) : null;

            if ($courseVip) {
                $vipStatus = $this->getVipService()->checkUserInMemberLevel($user['id'], $courseVip['id']);
            }
        }
        return $this->render('TopxiaWebBundle:CourseOrder:buy-modal.html.twig', array(
            'course'           => $course,
            'lessonId'         => $request->query->get('lessonId'),
            'payments'         => $this->getEnabledPayments(),
            'user'             => $userInfo,
            'noVerifiedMobile' => (strlen($user['verifiedMobile']) == 0),
            'verifiedMobile'   => (strlen($user['verifiedMobile']) > 0) ? $user['verifiedMobile'] : '',
            'avatarAlert'      => AvatarAlert::alertJoinCourse($user),
            'courseSetting'    => $courseSetting,
            'member'           => $member,
            'userFields'       => $userFields,
            'account'          => $account,
            'amount'           => $amount,
            'vipStatus'        => $vipStatus
        ));
    }

    public function modifyUserInfoAction(Request $request)
    {
        $formData = $request->request->all();

        $user = $this->getCurrentUser();

        if (empty($user)) {
            return $this->createMessageResponse('error', '用户未登录，不能购买。');
        }

        $course = $this->getCourseService()->getCourse($formData['targetId']);

        if (empty($course)) {
            return $this->createMessageResponse('error', '课程不存在，不能购买。');
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
        //判断用户是否为VIP
        $vipStatus = $courseVip = null;

        if ($this->isPluginInstalled('Vip') && $this->setting('vip.enabled')) {
            $courseVip = $course['vipLevelId'] > 0 ? $this->getLevelService()->getLevel($course['vipLevelId']) : null;

            if ($courseVip) {
                $vipStatus = $this->getVipService()->checkUserInMemberLevel($user['id'], $courseVip['id']);

                if ($vipStatus == 'ok') {
                    $data['becomeUseMember'] = true;
                }
            }
        }

        //免费课程,直接加入并进入课时
        $coinSetting   = $this->setting("coin");
        $courseSetting = $this->getSettingService()->get('course', array());
        $coinEnable    = isset($coinSetting["coin_enabled"]) && $coinSetting["coin_enabled"] == 1;
        //$userInfoEnable = isset($courseSetting['buy_fill_userinfo']) && $courseSetting['buy_fill_userinfo'] == 1;

        if (($coinEnable && isset($coinSetting['price_type']) && $coinSetting['price_type'] == "Coin" && $course['price'] == 0)
            || ((!isset($coinSetting['price_type']) || $coinSetting['price_type'] == "RMB") && $course['price'] == 0) || $vipStatus == 'ok') {
            $data['price']  = 0;
            $data['remark'] = '';
            $this->getCourseMemberService()->becomeStudentAndCreateOrder($user["id"], $course['id'], $data);

            if (isset($formData['lessonId']) && !empty($formData['lessonId'])) {
                return $this->redirect($this->generateUrl('course_learn', array('id' => $course['id'])).'#lesson/'.$formData['lessonId']);
            } else {
                return $this->redirect($this->generateUrl('course_show', array('id' => $course['id'])));
            }
        }

        return $this->redirect($this->generateUrl('order_show', array(
            'targetId'   => $formData['targetId'],
            'targetType' => 'course'
        )));
    }

    public function repayAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return $this->createMessageResponse('error', '用户未登录，不能支付。');
        }

        $order = $this->getOrderService()->getOrder($request->query->get('orderId'));

        if (empty($order)) {
            return $this->createMessageResponse('error', '订单不存在!');
        }

        if ((time() - $order['createdTime']) > 40 * 3600) {
            return $this->createMessageResponse('error', '订单已过期，不能支付，请重新创建订单。');
        }

        if ($order["userId"] != $user["id"]) {
            return $this->createMessageResponse('error', '不是您的订单，不能支付');
        }

        $course = $this->getCourseService()->getCourse($order['targetId']);

        if (empty($course)) {
            return $this->createMessageResponse('error', '购买的课程不存在，请重新创建订单!');
        }

        $result          = $this->getOrderInfo($order["targetId"]);
        $result["order"] = $order;

        return $this->render('TopxiaWebBundle:Order:order-create.html.twig', $result);
    }

    public function orderDetailAction(Request $request, $id)
    {
        $order = $this->getOrderService()->getOrder($id);

        if (empty($order)) {
            return $this->createMessageResponse('error', '订单不存在!');
        }

        $this->getCourseService()->tryManageCourse($order["targetId"]);

        return $this->forward('TopxiaWebBundle:Order:detail', array(
            'id' => $id
        ));
    }

    protected function getOrderInfo($id)
    {
        $course  = $this->getCourseService()->getCourse($id);
        $userIds = array();
        $userIds = array_merge($userIds, $course['teacherIds']);
        $users   = $this->getUserService()->findUsersByIds($userIds);

        $coinSetting = $this->getSettingService()->get("coin");
        $coinEnabled = isset($coinSetting["coin_enabled"]) && $coinSetting["coin_enabled"];
        $cashRate    = 1;

        if ($coinEnabled && array_key_exists("cash_rate", $coinSetting)) {
            $cashRate = $coinSetting["cash_rate"];
        }

        $coursePriceShowType = "RMB";

        if (array_key_exists("coin_enabled", $coinSetting)
            && $coinSetting["coin_enabled"] && array_key_exists("price_type", $coinSetting)) {
            $coursePriceShowType = $coinSetting["price_type"];
        }

        $user        = $this->getCurrentUser();
        $account     = $this->getCashAccountService()->getAccountByUserId($user["id"]);
        $accountCash = $account["cash"];

        $coinPayAmount = 0;
        $totalPrice    = 0;

        $hasPayPassword = strlen($user['payPassword']) > 0;

        if ($coursePriceShowType == "RMB") {
            $totalPrice = $course["price"];

            if ($totalPrice * 100 > $accountCash / $cashRate * 100) {
                $coinPayAmount = $accountCash;
            } else {
                $coinPayAmount = $totalPrice * $cashRate;
            }
        } else
        if ($coursePriceShowType == "Coin") {
            $totalPrice = $course["price"];

            if ($hasPayPassword && $totalPrice * 100 > $accountCash * 100) {
                $coinPayAmount = $accountCash;
            } else
            if ($hasPayPassword) {
                $coinPayAmount = $totalPrice;
            }
        }

        $couponApp = $this->getAppService()->findInstallApp("Coupon");

        return array(
            'account'             => $account,
            'couponApp'           => $couponApp,
            'cashRate'            => $cashRate,
            'hasPayPassword'      => $hasPayPassword,
            'totalPrice'          => $totalPrice,
            'coinPayAmount'       => $coinPayAmount,
            'targetIds'           => array($id),
            'targetTypes'         => array("course"),
            'coursePriceShowType' => $coursePriceShowType,

            'courses'             => empty($course) ? null : array($course),
            'users'               => empty($users) ? null : $users,
            'payUrl'              => 'course_order_pay'
        );
    }

    protected function getEnabledPayments()
    {
        $enableds = array();

        $setting = $this->setting('payment', array());

        if (empty($setting['enabled'])) {
            return $enableds;
        }

        $payment  = $this->get('topxia.twig.web_extension')->getDict('payment');
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
                $createdOrdersCount = $this->getOrderService()->searchOrderCount(array(
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

    protected function previewAsMember($as, $member, $course)
    {
        $user = $this->getCurrentUser();

        if (empty($user->id)) {
            return null;
        }

        if (in_array($as, array('member', 'guest'))) {
            if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {
                $member = array(
                    'id'          => 0,
                    'courseId'    => $course['id'],
                    'userId'      => $user['id'],
                    'levelId'     => 0,
                    'learnedNum'  => 0,
                    'isLearned'   => 0,
                    'seq'         => 0,
                    'isVisible'   => 0,
                    'role'        => 'teacher',
                    'locked'      => 0,
                    'createdTime' => time(),
                    'deadline'    => 0
                );
            }

            if (empty($member) || $member['role'] != 'teacher') {
                return $member;
            }

            if ($as == 'member') {
                $member['role'] = 'student';
            } else {
                $member = null;
            }
        }

        return $member;
    }

    public function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    public function getCourseOrderService()
    {
        return $this->getServiceKernel()->createService('Course.CourseOrderService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order.OrderService');
    }

    protected function getAuthService()
    {
        return $this->getServiceKernel()->createService('User.AuthService');
    }

    protected function getCashAccountService()
    {
        return $this->getServiceKernel()->createService('Cash.CashAccountService');
    }

    protected function getCashOrdersService()
    {
        return $this->getServiceKernel()->createService('Cash.CashOrdersService');
    }

    protected function getAppService()
    {
        return $this->getServiceKernel()->createService('CloudPlatform.AppService');
    }

    protected function getCouponService()
    {
        return $this->getServiceKernel()->createService('Coupon.CouponService');
    }

    protected function getUserFieldService()
    {
        return $this->getServiceKernel()->createService('User.UserFieldService');
    }

    protected function getLevelService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.LevelService');
    }

    protected function getVipService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.VipService');
    }

    protected function getCourseMemberService()
    {
        return $this->getServiceKernel()->createService('Course.CourseMemberService');
    }
}
