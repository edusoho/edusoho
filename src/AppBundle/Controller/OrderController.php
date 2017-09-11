<?php

namespace AppBundle\Controller;

use AppBundle\Common\MathToolkit;
use Biz\Cash\Service\CashService;
use Biz\Classroom\Service\ClassroomService;
use Biz\CloudPlatform\Service\AppService;
use Biz\Coupon\Service\CouponService;
use Biz\Course\Service\CourseService;
use Biz\Order\Service\OrderFacadeService;
use Biz\Order\Service\OrderService;
use AppBundle\Common\SmsToolkit;
use AppBundle\Common\ArrayToolkit;
use Codeages\Biz\Framework\Pay\Service\PayService;
use Symfony\Component\HttpFoundation\Request;
use VipPlugin\Biz\Vip\Service\LevelService;
use VipPlugin\Biz\Vip\Service\VipService;

class OrderController extends BaseController
{
    public function showAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();

        if (!$currentUser->isLogin()) {
            throw $this->createAccessDeniedException();
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
        $order = $order = MathToolkit::multiply($order, array('price_amount', 'pay_amount'), 0.01);

        preg_match('/管理员添加/', $order['title'], $order['edit']);
        $user = $this->getUserService()->getUser($order['user_id']);

        $orderLogs = $this->getOrderService()->findOrderLogsByOrderId($order['id']);

        $orderItems = $this->getOrderService()->findOrderItemsByOrderId($order['id']);

        $paymentTrade = $this->getPayService()->getTradeByTradeSn($order['trade_sn']);

        $orderDeducts = $this->getOrderService()->findOrderItemDeductsByOrderId($order['id']);

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($orderLogs, 'user_id'));

        return $this->render('order/detail-modal.html.twig', array(
            'order' => $order,
            'user' => $user,
            'orderLogs' => $orderLogs,
            'orderItems' => $orderItems,
            'paymentTrade' => $paymentTrade,
            'orderDeducts' => $orderDeducts,
            'users' => $users,
        ));
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
     * @return \Codeages\Biz\Framework\Order\Service\OrderService
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

    /**
     * @return OrderFacadeService
     */
    protected function getOrderFacadeService()
    {
        return $this->createService('Order:OrderFacadeService');
    }

    /**
     * @return PayService
     */
    protected function getPayService()
    {
        return $this->createService('Pay:PayService');
    }
}
