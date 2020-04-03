<?php

namespace Topxia\MobileBundleV2\Processor\Impl;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\ResourceKernel;
use Codeages\Biz\Pay\Service\PayService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Topxia\MobileBundleV2\Processor\BaseProcessor;
use Biz\Order\OrderProcessor\OrderProcessorFactory;
use Topxia\MobileBundleV2\Processor\OrderProcessor;
use Topxia\MobileBundleV2\Alipay\MobileAlipayConfig;

class OrderProcessorImpl extends BaseProcessor implements OrderProcessor
{
    public function getPaymentMode()
    {
        $coinSetting = $this->controller->setting('coin');

        $coinEnabled = true;
        if (empty($coinSetting)) {
            $coinEnabled = false;
        }

        $coinEnabled = isset($coinSetting['coin_enabled']) && $coinSetting['coin_enabled'];

        $payment = $this->controller->setting('payment', array());

        $apipayEnabled = true;
        if (empty($payment['enabled'])) {
            $apipayEnabled = false;
        }

        if (empty($payment['alipay_enabled'])) {
            $apipayEnabled = false;
        }

        if (empty($payment['alipay_key']) || empty($payment['alipay_secret']) || empty($payment['alipay_account'])) {
            $apipayEnabled = false;
        }

        //0 can buy
        $magicSetting = $this->getSettingService()->get('magic', array());
        $iosBuyDisable = isset($magicSetting['ios_buy_disable']) ? $magicSetting['ios_buy_disable'] : 0;

        return array(
            'coin' => $coinEnabled,
            'alipay' => $apipayEnabled,
            'ios_buy_disable' => $iosBuyDisable == 0,
        );
    }

    public function validateIAPReceipt()
    {
        $user = $this->controller->getUserByToken($this->request);

        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '您尚未登录');
        }

        $receipt = $this->getParam('receipt-data');
        $amount = $this->getParam('amount', 0);
        $transactionId = $this->getParam('transaction_id', false);

        $data = array(
            'receipt' => $receipt,
            'amount' => $amount,
            'transaction_id' => $transactionId,
            'is_sand_box' => false,
            'user_id' => $user['id'],
        );

        try {
            $trade = $this->getPayService()->rechargeByIap($data);

            return array(
                'status' => $trade,
            );
        } catch (\Exception $e) {
            return $this->createErrorResponse('error', $e->getMessage());
        }
    }

    private function getPayOrderInfo($targetType, $targetId)
    {
        $payOrderInfo = array();

        if ('course' == $targetType) {
            $course = $this->controller->getCourseService()->getCourse($targetId);

            if ($course['status'] != 'published') {
                return $this->createErrorResponse('course_close', '课程已关闭');
            }

            $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);

            $picture = empty($courseSet['cover']['middle']) ? '' : $courseSet['cover']['middle'];
            $payOrderInfo = array(
                'title' => $course['title'],
                'price' => $course['price'],
                'picture' => $this->coverPic($picture, 'course.png'),
            );
        } elseif ('classroom' == $targetType) {
            $classroom = $this->getClassroomService()->getClassRoom($targetId);

            if (empty($classroom)) {
                return $this->createErrorResponse('no_classroom', 'no_classroom');
            }

            $payOrderInfo = array(
                'title' => $classroom['title'],
                'price' => $classroom['price'],
                'picture' => $this->coverPic($classroom['middlePicture'], 'classroom.png'),
            );
        } elseif ('vip' == $targetType) {
            $result = $this->getVipOrderInfo($targetId);

            if (isset($result['error'])) {
                return $this->createErrorResponse($result['error']['name'], $result['error']['message']);
            }

            $payOrderInfo = $result;
        }

        return $payOrderInfo;
    }

    private function getVipOrderInfo($levelId)
    {
        if (!$this->controller->isinstalledPlugin('Vip')) {
            return $this->createErrorResponse('no_vip', '网校未安装vip插件');
        }

        $level = $this->getLevelService()->getLevel($levelId);

        $buyType = $this->controller->setting('vip.buyType');

        if (empty($buyType)) {
            $buyType = 10;
        }

        return array(
            'level' => $level,
            'buyType' => $buyType,
        );
    }

    public function getPayOrder()
    {
        $user = $this->controller->getUserByToken($this->request);

        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '您尚未登录');
        }

        $targetId = $this->getParam('targetId', 0);
        $targetType = $this->getParam('targetType');

        if (empty($targetId)) {
            return $this->createErrorResponse('not_tergetId', '没有发现购买内容！');
        }

        $payOrderInfo = $this->getPayOrderInfo($targetType, $targetId);

        if (isset($payOrderInfo['error'])) {
            return $this->createErrorResponse('error', '没有发现购买内容！');
        }

        $userProfile = $this->controller->getUserService()->getUserProfile($user['id']);

        foreach ($userProfile as $key => $value) {
            if (!in_array($key, array(
                'truename', 'id', 'mobile', 'qq', 'weixin', ))) {
                unset($userProfile[$key]);
            }
        }

        $coin = $this->getCoinSetting();

        return array(
            'userProfile' => $userProfile,
            'orderInfo' => $payOrderInfo,
            'coin' => $coin,
            'isInstalledCoupon' => $this->controller->isinstalledPlugin('Coupon'),
        );
    }

    private function getCoinSetting()
    {
        $coinSetting = $this->controller->setting('coin');

        if (empty($coinSetting)) {
            return null;
        }

        $coinEnabled = isset($coinSetting['coin_enabled']) && $coinSetting['coin_enabled'];

        if (empty($coinEnabled)) {
            return null;
        }

        $cashRate = 1;

        if (isset($coinSetting['cash_rate'])) {
            $cashRate = $coinSetting['cash_rate'];
        }

        $coin = array(
            'cashRate' => $cashRate,
            'priceType' => isset($coinSetting['price_type']) ? $coinSetting['price_type'] : null,
            'name' => isset($coinSetting['coin_name']) ? $coinSetting['coin_name'] : '虚拟币',
        );

        return $coin;
    }

    private function checkUserSetPayPassword($user, $newPayPassword)
    {
        $hasPayPassword = strlen($user['payPassword']) > 0;

        if ($hasPayPassword) {
            return;
        }

        $userPass = $user['nickname'];
        $this->controller->getAuthService()->changePayPassword($user['id'], $userPass, $newPayPassword);
    }

    private function initPayFieldsByTargetType($user, $targetType, $targetId)
    {
        $fields = $this->request->request->all();

        if ('vip' == $targetType) {
            $payVip = $this->controller->getLevelService()->getLevel($targetId);

            if (!$payVip) {
                return $this->createErrorResponse('error', '购买的vip类型不存在!');
            }

            $vip = $this->controller->getVipService()->getMemberByUserId($user['id']);

            if ($vip) {
                $currentVipLevel = $this->controller->getLevelService()->getLevel($vip['levelId']);

                if ($payVip['seq'] >= $currentVipLevel['seq']) {
                    $fields['buyType'] = 'upgrade';
                } else {
                    return $this->createErrorResponse('error', '会员类型不能降级付费!');
                }

                $fields['buyType'] = 'renew';
            } else {
                $fields['buyType'] = 'new';
            }
        }

        return $fields;
    }

    public function createOrder()
    {
        $targetType = $this->getParam('targetType');
        $targetId = $this->getParam('targetId');

        $user = $this->controller->getUserByToken($this->request);

        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '用户未登录，购买失败！');
        }

        try {
            /** @var $newApiResourceKernel ResourceKernel */
            $newApiResourceKernel = $this->controller->get('api_resource_kernel');

            $coinAmount = $this->getUseCoinAmount();
            $apiRequest = new ApiRequest(
                '/api/orders',
                'POST',
                array(),
                array(
                    'targetType' => $targetType,
                    'targetId' => $targetId,
                    'unencryptedPayPassword' => $this->getParam('payPassword'),
                    'couponCode' => $this->getParam('couponCode', ''),
                    'unit' => $this->getParam('unitType', ''),
                    'num' => $this->getParam('duration', 1),
                    'coinAmount' => $coinAmount,
                ),
                array()
            );

            $this->controller->getContainer()->get('api_firewall')->handle($this->request);
            $result = $newApiResourceKernel->handleApiRequest($apiRequest);
            $trade = $this->getPayService()->getTradeByTradeSn($result['sn']);
            if ($trade['status'] === 'paid') {
                return array('status' => 'ok', 'paid' => true, 'message' => '', 'payUrl' => '');
            } else {
                $platformCreatedResult = $this->getPayService()->getCreateTradeResultByTradeSnFromPlatform($result['sn']);

                return array('status' => 'ok', 'paid' => false, 'message' => '', 'payUrl' => $platformCreatedResult['url']);
            }
        } catch (\Exception $exception) {
            return $this->createErrorResponse('error', $this->controller->get('translator')->trans($exception->getMessage()));
        }
    }

    /**
     * @return PayService
     */
    private function getPayService()
    {
        return $this->controller->getService('Pay:PayService');
    }

    private function getUseCoinAmount()
    {
        $payment = $this->getParam('payment', 'alipay');
        $priceType = 'RMB';
        $coinSetting = $this->controller->setting('coin');
        $coinEnabled = isset($coinSetting['coin_enabled']) && $coinSetting['coin_enabled'];

        if ($coinEnabled && isset($coinSetting['price_type'])) {
            $priceType = $coinSetting['price_type'];
        }

        if ($payment == 'coin' && !$coinEnabled) {
            return $this->createErrorResponse('coin_close', '网校关闭了课程购买！');
        }

        $cashRate = 1;

        if ($coinEnabled && isset($coinSetting['cash_rate'])) {
            $cashRate = $coinSetting['cash_rate'];
        }

        $totalPrice = $this->getParam('totalPrice', 0);
        $coinPayAmount = 0;
        if ($payment == 'coin') {
            $coinPayAmount = round($totalPrice * $cashRate, 2);
        }

        return $coinPayAmount;
    }

    public function createOrderBack()
    {
        $targetType = $this->getParam('targetType');
        $targetId = $this->getParam('targetId');
        $payment = $this->getParam('payment', 'alipay');
        $fields = $this->request->request->all();

        if (empty($targetType) || empty($targetId) || !in_array($targetType, array('course', 'vip', 'classroom'))) {
            return $this->createErrorResponse('error', '参数不正确');
        }

        $user = $this->controller->getUserByToken($this->request);

        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '用户未登录，购买失败！');
        }

        $priceType = 'RMB';
        $coinSetting = $this->controller->setting('coin');
        $coinEnabled = isset($coinSetting['coin_enabled']) && $coinSetting['coin_enabled'];

        if ($coinEnabled && isset($coinSetting['price_type'])) {
            $priceType = $coinSetting['price_type'];
        }

        $cashRate = 1;

        if ($coinEnabled && isset($coinSetting['cash_rate'])) {
            $cashRate = $coinSetting['cash_rate'];
        }

        $fields = $this->initPayFieldsByTargetType($user, $targetType, $targetId);

        if (isset($fields['error'])) {
            return $this->createErrorResponse($fields['error']['name'], $fields['error']['message']);
        }

        if ($payment == 'coin') {
            try {
                $this->checkUserSetPayPassword($user, $fields['payPassword']);
            } catch (\Exception $e) {
                //return $this->createErrorResponse('error', "修改失败, 请在pc端修改支付密码!");
            }

            $fields['coinPayAmount'] = (float) $fields['totalPrice'] * (float) $cashRate;
        }

        if ($payment == 'coin' && !$coinEnabled) {
            return $this->createErrorResponse('coin_close', '网校关闭了课程购买！');
        }

        $processor = OrderProcessorFactory::create($targetType);

        try {
            if (!isset($fields['couponCode'])) {
                $fields['couponCode'] = '';
            }

            list($amount, $totalPrice, $couponResult) = $processor->shouldPayAmount($targetId, $priceType, $cashRate, $coinEnabled, $fields);

            if ($payment == 'coin' && !$this->isCanPayByCoin($totalPrice, $user['id'], $cashRate)) {
                return $this->createErrorResponse('coin_no_enough', '账户余额不足！');
            }

            $amount = (string) ((float) $amount);

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
                'payment' => empty($fields['payment']) ? 'alipay' : $fields['payment'],
                'targetId' => $targetId,
                'coupon' => empty($coupon) ? '' : $coupon,
                'couponDiscount' => empty($couponDiscount) ? 0 : $couponDiscount,
            );

            $order = $processor->createOrder($orderFileds, $fields);

            if ($order['amount'] == 0 && $order['coinAmount'] == 0) {
                $payData = array(
                    'sn' => $order['sn'],
                    'status' => 'success',
                    'amount' => $order['amount'],
                    'paidTime' => time(),
                );
                list($success, $order) = $this->getPayCenterService()->processOrder($payData);
            } elseif ($order['amount'] == 0 && $order['coinAmount'] > 0) {
                $payData = array(
                    'sn' => $order['sn'],
                    'status' => 'success',
                    'amount' => $order['amount'],
                    'paidTime' => time(),
                    'payment' => 'coin',
                );
                list($success, $order) = $this->getPayCenterService()->pay($payData);
                $processor = OrderProcessorFactory::create($order['targetType']);
            }

            if ($order['status'] == 'paid') {
                return array('status' => 'ok', 'paid' => true, 'message' => '', 'payUrl' => '');
            }

            return $this->payByAlipay($order, $this->controller->getToken($this->request));
        } catch (\Exception $e) {
            return $this->createErrorResponse('error', $e->getMessage());
        }
    }

    public function del_payClassRoom()
    {
        $targetType = $this->getParam('targetType');
        $targetId = $this->getParam('targetId');
        $payment = $this->getParam('payment', 'alipay');
        $fields = $this->request->request->all();

        if (empty($targetType) || empty($targetId) || !in_array($targetType, array('course', 'vip', 'classroom'))) {
            return $this->createErrorResponse('error', '参数不正确');
        }

        $user = $this->controller->getUserByToken($this->request);

        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '用户未登录，购买失败！');
        }

        $priceType = 'RMB';
        $coinSetting = $this->controller->setting('coin');
        $coinEnabled = isset($coinSetting['coin_enabled']) && $coinSetting['coin_enabled'];

        if ($coinEnabled && isset($coinSetting['price_type'])) {
            $priceType = $coinSetting['price_type'];
        }

        $cashRate = 1;

        if ($coinEnabled && isset($coinSetting['cash_rate'])) {
            $cashRate = $coinSetting['cash_rate'];
        }

        if ($payment == 'coin' && !$coinEnabled) {
            return $this->createErrorResponse('coin_close', '网校关闭了课程购买！');
        }

        $processor = OrderProcessorFactory::create($targetType);

        try {
            if (!isset($fields['couponCode'])) {
                $fields['couponCode'] = '';
            }

            list($amount, $totalPrice, $couponResult) = $processor->shouldPayAmount($targetId, $priceType, $cashRate, $coinEnabled, $fields);

            if ($payment == 'coin' && !$this->isCanPayByCoin($totalPrice, $user['id'], $cashRate)) {
                return $this->createErrorResponse('coin_no_enough', '账户余额不足！');
            }

            $amount = (string) ((float) $amount);

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
                'payment' => empty($fields['payment']) ? 'alipay' : $fields['payment'],
                'targetId' => $targetId,
                'coupon' => empty($coupon) ? '' : $coupon,
                'couponDiscount' => empty($couponDiscount) ? 0 : $couponDiscount,
            );

            $order = $processor->createOrder($orderFileds, $fields);

            if ($order['amount'] == 0 && $order['coinAmount'] == 0) {
                $payData = array(
                    'sn' => $order['sn'],
                    'status' => 'success',
                    'amount' => $order['amount'],
                    'paidTime' => time(),
                );
                list($success, $order) = $this->getPayCenterService()->processOrder($payData);
            } elseif ($order['amount'] == 0 && $order['coinAmount'] > 0) {
                $payData = array(
                    'sn' => $order['sn'],
                    'status' => 'success',
                    'amount' => $order['amount'],
                    'paidTime' => time(),
                    'payment' => 'coin',
                );
                list($success, $order) = $this->getPayCenterService()->pay($payData);
                $processor = OrderProcessorFactory::create($order['targetType']);
            }

            if ($order['status'] == 'paid') {
                return array('status' => 'ok', 'paid' => true, 'message' => '', 'payUrl' => '');
            }

            return $this->payByAlipay($order, $this->controller->getToken($this->request));
        } catch (\Exception $e) {
            return $this->createErrorResponse('error', $e->getMessage());
        }
    }

    private function isCanPayByCoin($totalPrice, $userId, $cashRate)
    {
        $cash = $this->getUserCoin($userId, $cashRate);

        return ($cash * 100) >= ($totalPrice * 100);
    }

    private function getUserCoin($userId, $cashRate)
    {
        $account = $this->getAccountService()->getUserBalanceByUserId($userId, true);

        if (empty($account)) {
            $account = $this->getAccountService()->createUserBalance($userId);
        }

        $cash = (float) $account['cash'] / (float) $cashRate;

        return $cash;
    }

    private function payByAlipay($order, $token)
    {
        $result = array('status' => 'error', 'message' => '', 'paid' => false, 'payUrl' => '');
        $payment = $this->controller->setting('payment', array());

        if (empty($payment['enabled'])) {
            $result['message'] = '支付功能未开启！';

            return $result;
        }

        if (empty($payment['alipay_enabled'])) {
            $result['message'] = '支付功能未开启！';

            return $result;
        }

        if (empty($payment['alipay_key']) || empty($payment['alipay_secret']) || empty($payment['alipay_account'])) {
            $result['message'] = '支付宝参数不正确！';

            return $result;
        }

        if (empty($payment['alipay_type']) || $payment['alipay_type'] != 'direct') {
            $payUrl = $this->controller->generateUrl('mapi_order_submit_pay_request', array('id' => $order['id'], 'token' => $token), UrlGeneratorInterface::ABSOLUTE_URL);
            $result['payUrl'] = $payUrl;
        } else {
            $result['payUrl'] = MobileAlipayConfig::createAlipayOrderUrl($this->request, 'edusoho', $order);
        }

        $result['status'] = 'ok';

        return $result;
    }

    private function getClassroomService()
    {
        return $this->controller->getService('Classroom:ClassroomService');
    }

    private function getLevelService()
    {
        return $this->controller->getService('VipPlugin:Vip:LevelService');
    }

    protected function getCourseSetService()
    {
        return $this->controller->getService('Course:CourseSetService');
    }
}
