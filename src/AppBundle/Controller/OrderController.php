<?php

namespace AppBundle\Controller;

use Biz\Cash\Service\CashAccountService;
use Biz\Cash\Service\CashService;
use Biz\Classroom\Service\ClassroomService;
use Biz\CloudPlatform\Service\AppService;
use Biz\Coupon\Service\CouponService;
use Biz\Course\Service\CourseService;
use Biz\Order\Service\OrderService;
use AppBundle\Common\SmsToolkit;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\NumberToolkit;
use AppBundle\Common\JoinPointToolkit;
use Symfony\Component\HttpFoundation\Request;
use Biz\Order\OrderProcessor\OrderProcessorFactory;
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
        $orderTypes = JoinPointToolkit::load('order');
        if (empty($targetType)
            || empty($targetId)
            || !array_key_exists($targetType, $orderTypes)
        ) {
            return $this->createMessageResponse('error', '参数不正确');
        }

        $processor = OrderProcessorFactory::create($targetType);
        $checkInfo = $processor->preCheck($targetId, $currentUser['id']);

        if (isset($checkInfo['error'])) {
            return $this->createMessageResponse('error', $checkInfo['error']);
        }

        $fields = $request->query->all();
        $orderInfo = $processor->getOrderInfo($targetId, $fields);

        if (((float) $orderInfo['totalPrice']) == 0) {
            $formData = array();
            $formData['userId'] = $currentUser['id'];
            $formData['targetId'] = $fields['targetId'];
            $formData['targetType'] = $fields['targetType'];
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

        $verifiedMobile = '';

        if ((isset($currentUser['verifiedMobile'])) && (strlen($currentUser['verifiedMobile']) > 0)) {
            $verifiedMobile = $currentUser['verifiedMobile'];
        }

        $orderInfo['verifiedMobile'] = $verifiedMobile;
        $orderInfo['hasPassword'] = strlen($currentUser['password']) > 0;

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

        if (isset($fields['coinPayAmount']) && !$this->canUseCoinPay($fields['coinPayAmount'], $user['id'])) {
            return $this->createMessageResponse('error', '当前使用的账户金额大于账户余额。');
        }

        if (!array_key_exists('targetId', $fields) || !array_key_exists('targetType', $fields)) {
            return $this->createMessageResponse('error', '订单中没有购买的内容，不能创建!');
        }

        $targetType = $fields['targetType'];
        $targetId = $fields['targetId'];

        $priceType = 'RMB';
        $coinSetting = $this->setting('coin');
        $coinEnabled = isset($coinSetting['coin_enabled']) && $coinSetting['coin_enabled'];

        if ($coinEnabled && isset($coinSetting['price_type'])) {
            $priceType = $coinSetting['price_type'];
        }

        $cashRate = 1;

        if ($coinEnabled && isset($coinSetting['cash_rate'])) {
            $cashRate = $coinSetting['cash_rate'];
        }

        $processor = OrderProcessorFactory::create($targetType);

        try {
            if (!isset($fields['couponCode']) || $fields['couponCode'] === '请输入优惠券') {
                $fields['couponCode'] = '';
            } else {
                $fields['couponCode'] = trim($fields['couponCode']);
            }

            list($amount, $totalPrice, $couponResult) = $processor->shouldPayAmount($targetId, $priceType, $cashRate, $coinEnabled, $fields);

            $amount = (string) ((float) $amount);
            $shouldPayMoney = (string) ((float) $fields['shouldPayMoney']);
            //价格比较

            if ((int)($totalPrice * 100) !== (int)($fields['totalPrice'] * 100)) {
                $this->createMessageResponse('error', '实际价格不匹配，不能创建订单!');
            }

            //价格比较

            if ((int)($amount * 100) !== (int)($shouldPayMoney * 100)) {
                return $this->createMessageResponse('error', '支付价格不匹配，不能创建订单!');
            }

            //虚拟币抵扣率比较
            $target = $processor->getTarget($targetId);

            $maxRate = $coinSetting['cash_model'] == 'deduction' && isset($target['maxRate']) ? $target['maxRate'] : 100;
            $priceCoin = $priceType == 'RMB' ? NumberToolkit::roundUp($totalPrice * $cashRate) : $totalPrice;

            if ($coinEnabled && isset($fields['coinPayAmount']) && ((int)((float) $fields['coinPayAmount'] * $maxRate) > (int)($priceCoin * $maxRate))) {
                return $this->createMessageResponse('error', '虚拟币抵扣超出限定，不能创建订单!');
            }

            if (isset($couponResult['useable']) && $couponResult['useable'] == 'yes') {
                $coupon = $fields['couponCode'];
                $couponDiscount = $couponResult['decreaseAmount'];
            }

            $orderFileds = array(
                'priceType' => $priceType,
                'totalPrice' => $totalPrice,
                'amount' => $amount,
                'coinRate' => $cashRate,
                'coinAmount' => empty($fields['coinPayAmount']) ? 0 : $fields['coinPayAmount'],
                'userId' => $user['id'],
                'payment' => 'none',
                'targetId' => $targetId,
                'coupon' => empty($coupon) ? '' : $coupon,
                'couponDiscount' => empty($couponDiscount) ? 0 : $couponDiscount,
            );

            $order = $processor->createOrder($orderFileds, $fields);
            if ($order['status'] == 'paid') {
                return $this->redirect($processor->callbackUrl($order, $this->container));
            }

            return $this->redirect($this->generateUrl('pay_center_show', array(
                'sn' => $order['sn'],
                'targetType' => $order['targetType'],
            )));
        } catch (\Exception $e) {
            return $this->createMessageResponse('error', $e->getMessage());
        }
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

    protected function canUseCoinPay($coinPayAmount, $userId)
    {
        $cashAccount = $this->getCashAccountService()->getAccountByUserId($userId, true);

        return !($coinPayAmount > $cashAccount['cash']);
    }

    protected function completeInfo($couponInfo, $code, $type)
    {
        $couponContents = array(
            'all' => '全站可用',
            'vip' => '全部会员',
            'course' => '全部课程',
            'classroom' => '全部班级',
        );

        $couponContent = '';
        $target = '';

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

    /**
     * @return CashAccountService
     */
    protected function getCashAccountService()
    {
        return $this->createService('Cash:CashAccountService');
    }
}
