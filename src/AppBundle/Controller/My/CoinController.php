<?php

namespace AppBundle\Controller\My;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\StringToolkit;
use Biz\Card\Service\CardService;
use Biz\User\Service\UserService;
use Biz\Coupon\Service\CouponService;
use Biz\System\Service\SettingService;
use Biz\CloudPlatform\Service\AppService;
use Biz\User\Service\InviteRecordService;
use Codeages\Biz\Pay\Service\AccountService;
use Codeages\Biz\Pay\Service\PayService;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\BaseController;
use Codeages\Biz\Order\Service\OrderService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CoinController extends BaseController
{
    public function indexAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return $this->createMessageResponse('error', '用户未登录，请先登录！');
        }

        $coinEnabled = $this->setting('coin.coin_enabled');

        if (empty($coinEnabled) || 0 == $coinEnabled) {
            return $this->createMessageResponse('error', '网校虚拟币未开启！');
        }

        $balance = $this->getAccountService()->getUserBalanceByUserId($user->id);
        $chargeCoin = $this->getAppService()->findInstallApp('ChargeCoin');

        $fields = $request->query->all();
        $conditions = array();

        if (!empty($fields)) {
            $conditions = $fields;
        }

        $conditions['amount_type'] = 'coin';
        $conditions['user_id'] = $user->id;
        $conditions['except_user_id'] = 0;

        $conditions['created_time_GTE'] = 0;
        $conditions['created_time_LTE'] = time();

        $paginator = new Paginator(
            $this->get('request'),
            $this->getAccountService()->countCashflows($conditions),
            20
        );

        $cashes = $this->getAccountService()->searchCashflows(
            $conditions,
            array('id' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $conditions['type'] = 'inflow';
        $amountInflow = $this->getAccountService()->sumColumnByConditions('amount', $conditions);

        $conditions['type'] = 'outflow';
        $amountOutflow = $this->getAccountService()->sumColumnByConditions('amount', $conditions);

        return $this->render('coin/index.html.twig', array(
            'balance' => $balance,
            'cashes' => $cashes,
            'paginator' => $paginator,
            'ChargeCoin' => $chargeCoin,
            'amountInflow' => $amountInflow ?: 0,
            'amountOutflow' => $amountOutflow ?: 0,
        ));
    }

    public function cashBillAction(Request $request)
    {
        return $this->redirect($this->generateUrl('my_orders'));
    }

    public function inviteCodeAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $inviteSetting = $this->getSettingService()->get('invite');

        if (empty($inviteSetting['invite_code_setting'])) {
            return $this->render('coin/invite-disable.html.twig');
        }

        if (!$user->isLogin()) {
            return $this->createMessageResponse('error', '用户未登录，请先登录！');
        }

        if (empty($user['inviteCode'])) {
            $user = $this->getUserService()->createInviteCode($user['id']);
        }

        $conditions = array('inviteUserId' => $user['id']);
        $recordCount = $this->getInviteRecordService()->countRecords($conditions);

        $paginator = new Paginator(
            $request,
            $recordCount,
            20
        );
        $records = $this->getInviteRecordService()->searchRecords(
            $conditions,
            array('inviteTime' => 'desc'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $invitedUsers = $coupons = array();
        if (!empty($records)) {
            $userIds = ArrayToolkit::column($records, 'invitedUserId');
            $invitedUsers = $this->getUserService()->findUsersByIds($userIds);
            $records = ArrayToolkit::index($records, 'invitedUserId');

            // record的invitedUserCardId = card的cardId = coupon的Id
            $couponIds = ArrayToolkit::column($records, 'inviteUserCardId');
            $coupons = $this->getCouponService()->findCouponsByIds($couponIds);
        }

        $myRecord = $this->getInviteRecordService()->getRecordByInvitedUserId($user['id']);

        $site = $this->getSettingService()->get('site', array());

        $registerUrl = $this->generateUrl('register', array('inviteCode' => $user['inviteCode']), UrlGeneratorInterface::ABSOLUTE_URL);

        if (isset($inviteSetting['inviteInfomation_template'])) {
            $variables = array(
                'siteName' => $site['name'],
                'registerUrl' => $registerUrl,
            );
            $message = StringToolkit::template($inviteSetting['inviteInfomation_template'], $variables);
        }

        $couponRateSum = $this->getInviteRecordService()->sumCouponRateByInviteUserId($user['id']);

        return $this->render('coin/invite-code.html.twig', array(
            'code' => $user['inviteCode'],
            'myRecord' => $myRecord,
            'recordCount' => $recordCount,
            'records' => $records,
            'inviteSetting' => $inviteSetting,
            'invitedUsers' => $invitedUsers,
            'paginator' => $paginator,
            'coupons' => $coupons,
            'inviteInfomation_template' => $message,
            'couponRateSum' => $couponRateSum,
        ));
    }

    public function receiveCouponAction(Request $request)
    {
        $user = $this->getCurrentUser();

        $record = $this->getInviteRecordService()->getRecordByInvitedUserId($user['id']);

        $response = $this->redirect($this->generateUrl('my_cards', array('cardType' => 'coupon', 'cardId' => $record['invitedUserCardId'])));
        $response->headers->setCookie(new Cookie('modalOpened', '1'));

        return $response;
    }

    public function showAction(Request $request)
    {
        $coinSetting = $this->getSettingService()->get('coin', array());

        if (isset($coinSetting['coin_content'])) {
            $content = $coinSetting['coin_content'];
        } else {
            $content = '';
        }

        return $this->render('coin/coin-content-show.html.twig', array(
            'content' => $content,
            'coinSetting' => $coinSetting,
        ));
    }

    private function sendInviteUserCard($inviteUserId, $invitedUserId)
    {
        $inviteSetting = $this->setting('invite');

        if (isset($inviteSetting['get_coupon_setting']) && 0 == $inviteSetting['get_coupon_setting']) {
            $inviteCoupon = $this->getCouponService()->generateInviteCoupon($inviteUserId, 'pay');

            if (!empty($inviteCoupon)) {
                $this->getInviteRecordService()->addInviteRewardRecordToInvitedUser($invitedUserId, array('inviteUserCardId' => $inviteCoupon['id']));
            }
        }
    }

    protected function caculate($amount, $canChange, $data)
    {
        $coinSetting = $this->getSettingService()->get('coin', array());

        $coinRanges = $coinSetting['coin_consume_range_and_present'];

        if ($coinRanges == array(array(0, 0))) {
            return array($amount, $canChange, $data);
        }

        $ranges = array();
        for ($i = 0; $i < count($coinRanges); ++$i) {
            $consume = $coinRanges[$i][0];
            $change = $coinRanges[$i][1];

            foreach ($coinRanges as $key => $range) {
                if ($change == $range[1] && $consume > $range[0]) {
                    $consume = $range[0];
                }
            }

            $ranges[] = array($consume, $change);
        }

        $ranges = ArrayToolkit::index($ranges, 1);

        $send = 0;
        $bottomConsume = 0;

        foreach ($ranges as $key => $range) {
            if ($amount >= $range[0] && $send < $range[1]) {
                $send = $range[1];
            }

            if ($bottomConsume > $range[0] || 0 == $bottomConsume) {
                $bottomConsume = $range[0];
            }
        }

        if (isset($ranges[$send]) && $amount >= $ranges[$send][0]) {
            $canUseAmount = $amount - $ranges[$send][0];
            $canChange += $send;
        } else {
            $canUseAmount = $amount;
            $canChange += $send;
        }

        if ($send > 0) {
            $data[] = array(
                'send' => sprintf('消费满%s元送%s', $ranges[$send][0], $ranges[$send][1]),
                'sendAmount' => "{$ranges[$send][1]}", );
        }

        if ($canUseAmount >= $bottomConsume) {
            list($canUseAmount, $canChange, $data) = $this->caculate($canUseAmount, $canChange, $data);
        }

        return array($canUseAmount, $canChange, $data);
    }

    public function resultNoticeAction(Request $request)
    {
        return $this->render('coin/retrun-notice.html.twig');
    }

    /**
     * @return CouponService
     */
    protected function getCouponService()
    {
        return $this->getBiz()->service('Coupon:CouponService');
    }

    /**
     * @return CardService
     */
    protected function getCardService()
    {
        return $this->getBiz()->service('Card:CardService');
    }

    /**
     * @return InviteRecordService
     */
    protected function getInviteRecordService()
    {
        return $this->getBiz()->service('User:InviteRecordService');
    }

    /**
     * @return OrderService
     */
    protected function getOrderService()
    {
        return $this->getBiz()->service('Order:OrderService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }

    /**
     * @return AppService
     */
    protected function getAppService()
    {
        return $this->getBiz()->service('CloudPlatform:AppService');
    }

    /**
     * @return AccountService
     */
    protected function getAccountService()
    {
        return $this->getBiz()->service('Pay:AccountService');
    }

    /**
     * @return PayService
     */
    protected function getPayService()
    {
        return $this->getBiz()->service('Pay:PayService');
    }
}
