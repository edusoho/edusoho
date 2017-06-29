<?php

namespace AppBundle\Controller;

use Biz\Cash\Service\CashService;
use Biz\Classroom\Service\ClassroomService;
use Biz\CloudPlatform\Service\AppService;
use Biz\Coupon\Service\CouponService;
use Biz\Course\Service\CourseService;
use Biz\Order\Service\OrderService;
use AppBundle\Common\SmsToolkit;
use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use VipPlugin\Biz\Vip\Service\LevelService;
use VipPlugin\Biz\Vip\Service\VipService;

class OrderController extends BaseController
{
    public function showAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();

        if (!$currentUser->isLogin()) {
            return $this->redirect($this->generateUrl('login'));
        }

        $targetType = $request->query->get('targetType');
        $targetId = $request->query->get('targetId');
        $fields = $request->query->all();
        list($error, $orderInfo, $processor) = $this->getOrderFacadeService()->getOrderInfo($targetType, $targetId, $fields);

        if (isset($error['error'])) {
            return $this->createMessageResponse('error', $error['error']);
        }

        if (((float) $orderInfo['totalPrice']) == 0) {
            $formData = array();
            $formData['userId'] = $currentUser['id'];
            $formData['targetId'] = $targetId;
            $formData['targetType'] = $targetType;
            $formData['amount'] = 0;
            $formData['totalPrice'] = 0;
            $coinSetting = $this->setting('coin');
            $formData['priceType'] = empty($coinSetting['priceType']) ? 'RMB' : $coinSetting['priceType'];
            $formData['coinRate'] = empty($coinSetting['coinRate']) ? 1 : $coinSetting['coinRate'];
            $formData['coinAmount'] = 0;
            $formData['payment'] = 'alipay';
            $order = $processor->createOrder($formData, $fields);
            if ($order['status'] == 'paid') {
                return $this->redirect($processor->callbackUrl($order, $this->container));
            }
        }

        return $this->render('order/order-create.html.twig', $orderInfo);
    }

    public function smsVerificationAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();
        $verifiedMobile = '';

        if ((isset($currentUser['verifiedMobile'])) && (strlen($currentUser['verifiedMobile']) > 0)) {
            $verifiedMobile = $currentUser['verifiedMobile'];
        }

        return $this->render('order/order-sms-modal.html.twig', array(
            'verifiedMobile' => $verifiedMobile,
        ));
    }

    public function createAction(Request $request)
    {
        $fields = $request->request->all();

        if (isset($fields['coinPayAmount']) && $fields['coinPayAmount'] < 0) {
            return $this->createMessageResponse('error', '虚拟币填写不正确');
        }

        if (isset($fields['coinPayAmount']) && $fields['coinPayAmount'] > 0) {
            $scenario = 'sms_user_pay';

            if ($this->setting('cloud_sms.sms_enabled') == '1' && $this->setting("cloud_sms.{$scenario}") == 'on') {
                list($result, $sessionField, $requestField) = SmsToolkit::smsCheck($request, $scenario);

                if (!$result) {
                    return $this->createMessageResponse('error', '短信验证失败。');
                }
            }
        }

        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return $this->createMessageResponse('error', '用户未登录，创建订单失败。');
        }

        $targetType = $fields['targetType'];
        $targetId = $fields['targetId'];

        if (!isset($fields['couponCode']) || $fields['couponCode'] === '请输入优惠券') {
            $fields['couponCode'] = '';
        } else {
            $fields['couponCode'] = trim($fields['couponCode']);
        }

        try {
            list($order, $processor) = $this->getOrderFacadeService()->createOrder($targetType, $targetId, $fields);
        } catch (\Exception $e) {
            return $this->createMessageResponse('error', $e->getMessage());
        }

        if ($order['status'] == 'paid') {
            return $this->redirect($processor->callbackUrl($order, $this->container));
        }

        return $this->redirect($this->generateUrl('pay_center_show', array(
            'sn' => $order['sn'],
            'targetType' => $order['targetType'],
        )));
    }

    public function detailAction(Request $request, $id)
    {
        $order = $this->getOrderService()->getOrder($id);

        preg_match('/管理员添加/', $order['title'], $order['edit']);
        $user = $this->getUserService()->getUser($order['userId']);

        $orderLogs = $this->getOrderService()->findOrderLogs($order['id']);

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($orderLogs, 'userId'));

        return $this->render('order/detail-modal.html.twig', array(
            'order' => $order,
            'user' => $user,
            'orderLogs' => $orderLogs,
            'users' => $users,
        ));
    }

    public function couponCheckAction(Request $request, $type, $id)
    {
        if ($request->getMethod() == 'POST') {
            $code = trim($request->request->get('code'));

            if (!in_array($type, array('course', 'vip', 'classroom'))) {
                throw new \RuntimeException('优惠券不支持的购买项目。');
            }

            $price = $request->request->get('amount');

            $couponInfo = $this->getCouponService()->checkCouponUseable($code, $type, $id, $price);
            $couponInfo = $this->completeInfo($couponInfo, $code, $type);

            return $this->createJsonResponse($couponInfo);
        }
    }

    protected function completeInfo($couponInfo, $code, $type)
    {
        if ($couponInfo['useable'] == 'no' && !empty($couponInfo['message'])) {
            return $couponInfo;
        }

        $coupon = $this->getCouponService()->getCouponByCode($code);
        $targetId = $coupon['targetId'];
        $couponType = $coupon['targetType'];

        if ($couponType == 'course') {
            if ($targetId != 0) {
                $course = $this->getCourseService()->getCourse($targetId);
                $couponContent = '课程:'.$course['title'];
                $url = $this->generateUrl('course_show', array('id' => $targetId));
                $target = "<a href='{$url}' target='_blank'>{$couponContent}</a>";
            } else {
                $couponContent = '全部课程';
                $url = $this->generateUrl('course_set_explore');
                $target = "<a href='{$url}' target='_blank'>{$couponContent}</a>";
            }

            $couponInfo['message'] = "无法使用{$code}优惠券,该优惠券只能用于{$target}";

            return $couponInfo;
        }

        if ($couponType == 'classroom') {
            if ($targetId != 0) {
                $classroom = $this->getClassroomService()->getClassroom($targetId);
                $couponContent = '班级:'.$classroom['title'];
                $url = $this->generateUrl('classroom_introductions', array('id' => $targetId));
                $target = "<a href='{$url}' target='_blank'>{$couponContent}</a>";
            } else {
                $couponContent = '全部班级';
                $url = $this->generateUrl('classroom_explore');
                $target = "<a href='{$url}' target='_blank'>{$couponContent}</a>";
            }

            $couponInfo['message'] = "无法使用{$code}优惠券,该优惠券只能用于《{$target}》";

            return $couponInfo;
        }

        if ($couponType == 'vip' && $this->isPluginInstalled('Vip')) {
            if ($targetId != 0) {
                $level = $this->getLevelService()->getLevel($targetId);
                $couponContent = '会员:'.$level['name'];
            } else {
                $couponContent = '全部VIP';
            }

            $url = $this->generateUrl('vip');
            $target = "<a href='{$url}' target='_blank'>{$couponContent}</a >";

            $couponInfo['message'] = "无法使用{$code}优惠券,该优惠券只能用于《{$target}》";

            return $couponInfo;
        }

        return $couponInfo;
    }

    /**
     * @return LevelService
     */
    protected function getLevelService()
    {
        return $this->getBiz()->service('VipPlugin:Vip:LevelService');
    }

    /**
     * @return AppService
     */
    protected function getAppService()
    {
        return $this->getBiz()->service('CloudPlatform:AppService');
    }

    /**
     * @return CashService
     */
    protected function getCashService()
    {
        return $this->getBiz()->service('Cash:CashService');
    }

    /**
     * @return OrderService
     */
    protected function getOrderService()
    {
        return $this->getBiz()->service('Order:OrderService');
    }

    /**
     * @return CouponService
     */
    protected function getCouponService()
    {
        return $this->getBiz()->service('Coupon:CouponService');
    }

    /**
     * @return VipService
     */
    protected function getVipService()
    {
        return $this->getBiz()->service('VipPlugin:Vip:VipService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->getBiz()->service('Classroom:ClassroomService');
    }

    protected function getOrderFacadeService()
    {
        return $this->createService('Order:OrderFacadeService');
    }
}
