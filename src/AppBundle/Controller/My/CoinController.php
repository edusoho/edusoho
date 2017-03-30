<?php

namespace AppBundle\Controller\My;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\StringToolkit;
use Biz\Card\Service\CardService;
use Biz\Cash\Service\CashService;
use Biz\User\Service\UserService;
use Biz\Order\Service\OrderService;
use Biz\Coupon\Service\CouponService;
use Biz\System\Service\SettingService;
use Biz\Cash\Service\CashOrdersService;
use Biz\Cash\Service\CashAccountService;
use Biz\CloudPlatform\Service\AppService;
use Biz\User\Service\InviteRecordService;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\BaseController;

class CoinController extends BaseController
{
    public function indexAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return $this->createMessageResponse('error', '用户未登录，请先登录！');
        }

        $coinEnabled = $this->setting('coin.coin_enabled');

        if (empty($coinEnabled) || $coinEnabled == 0) {
            return $this->createMessageResponse('error', '网校虚拟币未开启！');
        }

        $account = $this->getCashAccountService()->getAccountByUserId($user->id, true);

        $chargeCoin = $this->getAppService()->findInstallApp('ChargeCoin');

        if (empty($account)) {
            $this->getCashAccountService()->createAccount($user->id);
        }

        $fields = $request->query->all();
        $conditions = array();

        if (!empty($fields)) {
            $conditions = $fields;
        }

        $conditions['cashType'] = 'Coin';
        $conditions['userId'] = $user->id;

        $conditions['startTime'] = 0;
        $conditions['endTime'] = time();

        switch ($request->get('lastHowManyMonths')) {
            case 'oneWeek':
                $conditions['startTime'] = $conditions['endTime'] - 7 * 24 * 3600;
                break;
            case 'twoWeeks':
                $conditions['startTime'] = $conditions['endTime'] - 14 * 24 * 3600;
                break;
            case 'oneMonth':
                $conditions['startTime'] = $conditions['endTime'] - 30 * 24 * 3600;
                break;
            case 'twoMonths':
                $conditions['startTime'] = $conditions['endTime'] - 60 * 24 * 3600;
                break;
            case 'threeMonths':
                $conditions['startTime'] = $conditions['endTime'] - 90 * 24 * 3600;
                break;
        }

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCashService()->searchFlowsCount($conditions),
            20
        );

        $cashes = $this->getCashService()->searchFlows(
            $conditions,
            array('id' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $conditions['type'] = 'inflow';
        $amountInflow = $this->getCashService()->analysisAmount($conditions);

        $conditions['type'] = 'outflow';
        $amountOutflow = $this->getCashService()->analysisAmount($conditions);

// $amount=$this->getOrderService()->analysisAmount(array('userId'=>$user->id,'status'=>'paid'));
        // $amount+=$this->getCashOrdersService()->analysisAmount(array('userId'=>$user->id,'status'=>'paid'));
        return $this->render('coin/index.html.twig', array(
            'account' => $account,
            'cashes' => $cashes,
            'paginator' => $paginator,
            // 'amount'=>$amount,
            'ChargeCoin' => $chargeCoin,
            'amountInflow' => $amountInflow ?: 0,
            'amountOutflow' => $amountOutflow ?: 0,
        ));
    }

    public function cashBillAction(Request $request)
    {
        $user = $this->getCurrentUser();

        $conditions = array(
            'userId' => $user['id'],
        );

        $conditions['cashType'] = 'RMB';
        $conditions['startTime'] = 0;
        $conditions['endTime'] = time();

        switch ($request->get('lastHowManyMonths')) {
            case 'oneWeek':
                $conditions['startTime'] = $conditions['endTime'] - 7 * 24 * 3600;
                break;
            case 'twoWeeks':
                $conditions['startTime'] = $conditions['endTime'] - 14 * 24 * 3600;
                break;
            case 'oneMonth':
                $conditions['startTime'] = $conditions['endTime'] - 30 * 24 * 3600;
                break;
            case 'twoMonths':
                $conditions['startTime'] = $conditions['endTime'] - 60 * 24 * 3600;
                break;
            case 'threeMonths':
                $conditions['startTime'] = $conditions['endTime'] - 90 * 24 * 3600;
                break;
        }

        $paginator = new Paginator(
            $request,
            $this->getCashService()->searchFlowsCount($conditions),
            20
        );

        $cashes = $this->getCashService()->searchFlows(
            $conditions,
            array('id' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $conditions['type'] = 'inflow';
        $amountInflow = $this->getCashService()->analysisAmount($conditions);

        $conditions['type'] = 'outflow';
        $amountOutflow = $this->getCashService()->analysisAmount($conditions);

        return $this->render('coin/cash_bill.html.twig', array(
            'cashes' => $cashes,
            'paginator' => $paginator,
            'amountInflow' => $amountInflow ?: 0,
            'amountOutflow' => $amountOutflow ?: 0,
        ));
    }

    public function inviteCodeAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $inviteReward = array();
        $promote = array();

        if (!$user->isLogin()) {
            return $this->createMessageResponse('error', '用户未登录，请先登录！');
        }

        $inviteSetting = $this->getSettingService()->get('invite', array());

        if (empty($user['inviteCode'])) {
            $user = $this->getUserService()->createInviteCode($user['id']);
        }

        $invitedUserIds = $this->getUserService()->findUserIdsByInviteCode($user['inviteCode']);
        $invitedUsers = null;

        if (!empty($invitedUserIds)) {
            $conditions = array('userIds' => $invitedUserIds);
            $paginator = new Paginator(
                $request,
                $this->getUserService()->countUsers($conditions),
                20
            );

            $invitedUsers = $this->getUserService()->searchUsers(
                $conditions,
                array('id' => 'DESC'),
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
            );
            $recordTime = $this->getInviteTime(ArrayToolkit::column($invitedUsers, 'id'));

            for ($i = 0; $i < count($invitedUsers); ++$i) {
                $invitedUsers[$i]['inviteTime'] = $recordTime[$i];
                $record = $this->getInviteRecordService()->getRecordByInvitedUserId($invitedUsers[$i]['id']);
                $card = $this->getCardService()->getCardByCardId($record['inviteUserCardId']);
                $coupon = $this->getCouponService()->getCoupon($card['cardId']);
                $invitedUsers[$i]['rewardRate'] = $coupon['rate'];

                if ($record['inviteUserCardId']) {
                    $invitedUsers[$i]['inviteRewardTime'] = date('Y-m-d H:i:s', $coupon['createdTime']);
                }
            }
        } else {
            $paginator = new Paginator(
                $request,
                0,
                20
            );
        }

        $record = $this->getInviteRecordService()->getRecordByInvitedUserId($user['id']);

        $message = null;
        $site = $this->getSettingService()->get('site', array());
        $inviteSetting = $this->getSettingService()->get('invite', array());

        $urlContent = $this->generateUrl('register', array(), true);
        $registerUrl = $urlContent.'?inviteCode='.$user['inviteCode'];

        if ($inviteSetting['inviteInfomation_template']) {
            $variables = array(
                'siteName' => $site['name'],
                'registerUrl' => $registerUrl,
            );
            $message = StringToolkit::template($inviteSetting['inviteInfomation_template'], $variables);
        }

        return $this->render('coin/invite-code.html.twig', array(
            'inviteInfomation_template' => $message,
            'code' => $user['inviteCode'],
            'record' => $record,
            'inviteSetting' => $inviteSetting,
            'invitedUsers' => $invitedUsers,
            'inviteReward' => $inviteReward,
            'paginator' => $paginator,
        ));
    }

    public function promoteLinkAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $message = null;
        $site = $this->getSettingService()->get('site', array());
        $inviteSetting = $this->getSettingService()->get('invite', array());

        $urlContent = $this->generateUrl('register', array(), true);
        $registerUrl = $urlContent.'?inviteCode='.$user['inviteCode'];

        if ($inviteSetting['inviteInfomation_template']) {
            $variables = array(
                'siteName' => $site['name'],
                'registerUrl' => $registerUrl,
            );
            $message = StringToolkit::template($inviteSetting['inviteInfomation_template'], $variables);
        }

        return $this->render('coin/promote-link-modal.html.twig',
            array(
                'code' => $user['inviteCode'],
                'inviteInfomation_template' => $message,
            ));
    }

    private function getInviteTime($userIds)
    {
        $recordTime = array();

        foreach ($userIds as $key => $id) {
            $record = $this->getInviteRecordService()->getRecordByInvitedUserId($id);
            $recordTime[] = $record['inviteTime'];
        }

        return $recordTime;
    }

    public function writeInvitecodeAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if ($request->getMethod() == 'POST') {
            $fields = $request->request->all();
            $inviteCode = $fields['inviteCode'];

            $record = $this->getInviteRecordService()->getRecordByInvitedUserId($user['id']);

            if ($record) {
                $response = array('success' => false, 'message' => '您已经填过邀请码');
            } else {
                $promoteUser = $this->getUserService()->getUserByInviteCode($inviteCode);

                if ($promoteUser) {
                    if ($promoteUser['id'] == $user['id']) {
                        $response = array('success' => false, 'message' => '不能填写自己的邀请码');
                    } else {
                        $this->getInviteRecordService()->createInviteRecord($promoteUser['id'], $user['id']);
                        $response = array('success' => true);
                        $inviteCoupon = $this->getCouponService()->generateInviteCoupon($user['id'], 'register');

                        if (!empty($inviteCoupon)) {
                            $card = $this->getCardService()->getCardByCardId($inviteCoupon['id']);
                            $this->getInviteRecordService()->addInviteRewardRecordToInvitedUser($user['id'], array('invitedUserCardId' => $card['cardId']));
                            $this->sendInviteUserCard($promoteUser['id'], $user['id']);
                        }
                    }
                } else {
                    $response = array('success' => false, 'message' => '邀请码不正确');
                }
            }

            return $this->createJsonResponse($response);
        }

        return $this->render('coin/write-invitecode-modal.html.twig');
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

        if (isset($inviteSetting['get_coupon_setting']) && $inviteSetting['get_coupon_setting'] == 0) {
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

            if ($bottomConsume > $range[0] || $bottomConsume == 0) {
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

    public function payAction(Request $request)
    {
        $formData = $request->request->all();
        $user = $this->getCurrentUser();
        $formData['userId'] = $user['id'];

        $order = $this->getCashOrdersService()->addOrder($formData);

        return $this->redirect($this->generateUrl('pay_center_show', array(
            'sn' => $order['sn'],
            'targetType' => $order['targetType'],
        )));
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
     * @return CashService
     */
    protected function getCashService()
    {
        return $this->getBiz()->service('Cash:CashService');
    }

    /**
     * @return CashAccountService
     */
    protected function getCashAccountService()
    {
        return $this->getBiz()->service('Cash:CashAccountService');
    }

    /**
     * @return CashOrdersService
     */
    protected function getCashOrdersService()
    {
        return $this->getBiz()->service('Cash:CashOrdersService');
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
}
