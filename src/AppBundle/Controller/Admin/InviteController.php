<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\ExportHelp;

class InviteController extends BaseController
{
    public function recordAction(Request $request)
    {
        $conditions = $request->query->all();
        $conditions = ArrayToolkit::parts($conditions, array('nickname', 'startDate', 'endDate'));

        $page = $request->query->get('page', 0);

        if (!empty($conditions['nickname']) && empty($page)) {
            $user = $this->getUserService()->getUserByNickname($conditions['nickname']);

            $conditions['inviteUserId'] = empty($user) ? '0' : $user['id'];

            $invitedRecord = $this->getInviteRecordService()->getRecordByInvitedUserId($user['id']);
            unset($conditions['nickname']);
        }

        $recordCount = $this->getInviteRecordService()->countRecords($conditions);
        $paginator = new Paginator(
            $this->get('request'),
            $recordCount,
            20
        );

        $inviteRecords = $this->getInviteRecordService()->searchRecords(
            $conditions,
            array(),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        if (!empty($invitedRecord)) {
            array_unshift($inviteRecords, $invitedRecord);
        }

        foreach ($inviteRecords as &$record) {
            list($coinAmountTotalPrice, $amountTotalPrice, $totalPrice) = $this->getUserOrderDataByUserIdAndTime($record['invitedUserId'], $record['inviteTime']);
            $record['coinAmountTotalPrice'] = $coinAmountTotalPrice;
            $record['amountTotalPrice'] = $amountTotalPrice;
            $record['totalPrice'] = $totalPrice;
        }

        $users = $this->getAllUsersByRecords($inviteRecords);

        return $this->render('admin/invite/records.html.twig', array(
            'records' => $inviteRecords,
            'users' => $users,
            'paginator' => $paginator,
        ));
    }

    public function preExportRecordDataAction(Request $request)
    {
        list($start, $limit, $exportAllowCount) = ExportHelp::getMagicExportSetting($request);

        $conditions = $request->query->all();
        $conditions = ArrayToolkit::parts($conditions, array('nickname', 'startDate', 'endDate'));

        if (!empty($conditions['nickname'])) {
            $user = $this->getUserService()->getUserByNickname($conditions['nickname']);
            $conditions['inviteUserId'] = empty($user) ? '0' : $user['id'];

            $invitedRecord = $this->getInviteRecordService()->getRecordByInvitedUserId($user['id']);

            if (!empty($invitedRecord)) {
                $users = $this->getAllUsersByRecords(array($invitedRecord));
                $invitedExportContent = $this->exportDataByRecord($invitedRecord, $users);
            }

            unset($conditions['nickname']);
        }


        list($records, $recordCount) = $this->getExportRecordContent(
            $start,
            $limit,
            $conditions,
            $exportAllowCount
        );

        $title = '邀请人,注册用户,订单消费总额,订单虚拟币总额,订单现金总额,邀请码,邀请时间';
        $file = '';
        if ($start == 0) {
            $file = ExportHelp::addFileTitle($request, 'invite_record', $title);
            if (!empty($invitedExportContent)) {
                $file = ExportHelp::saveToTempFile($request, $invitedExportContent, $file);
            }
        }

        $content = implode("\r\n", $records);
        $file = ExportHelp::saveToTempFile($request, $content, $file);

        $status = ExportHelp::getNextMethod($start + $limit, $recordCount);

        return $this->createJsonResponse(
            array(
                'status' => $status,
                'fileName' => $file,
                'start' => $start + $limit,
            )
        );
    }

    public function getExportRecordContent($start, $limit, $conditions, $exportAllowCount)
    {
        $recordCount = $this->getInviteRecordService()->countRecords($conditions);

        $recordCount = ($recordCount > $exportAllowCount) ? $exportAllowCount : $recordCount;
        if ($recordCount < ($start + $limit + 1)) {
            $limit = $recordCount - $start;
        }

        $recordData = array();
        $records = $this->getInviteRecordService()->searchRecords(
            $conditions,
            array(),
            $start,
            $limit
        );
        $users = $this->getAllUsersByRecords($records);

        foreach ($records as $record) {
            $content = $this->exportDataByRecord($record, $users);
            $recordData[] = $content;
        }
        return array($recordData, $recordCount);
    }

    protected function exportDataByRecord($record, $users)
    {
        list($coinAmountTotalPrice, $amountTotalPrice, $totalPrice) = $this->getUserOrderDataByUserIdAndTime($record['invitedUserId'], $record['inviteTime']);
        $content = '';
        $content .= $users[$record['inviteUserId']]['nickname'].',';
        $content .= $users[$record['invitedUserId']]['nickname'].',';
        $content .= $totalPrice.',';
        $content .= $coinAmountTotalPrice.',';
        $content .= $amountTotalPrice.',';
        $content .= $users[$record['inviteUserId']]['inviteCode'].',';
        $content .= date('Y-m-d H:i:s',$record['inviteTime']).',';
        return $content;
    }

    public function exportRecordDataAction(Request $request)
    {
        $fileName = sprintf('invite-record-(%s).csv', date('Y-n-d'));
        return ExportHelp::exportCsv($request, $fileName);
    }

    public function indexAction(Request $request)
    {
        $conditions = $request->query->all();
        $conditions = ArrayToolkit::parts($conditions, array('nickname'));
        $paginator = new Paginator(
            $this->get('request'),
            $this->getUserService()->countUsers($conditions),
            20
        );

        $users = $this->getUserService()->searchUsers(
            $conditions,
            array('id' => 'ASC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $inviteInformations = array();

        foreach ($users as $key => $user) {
            $invitedRecords = $this->getInviteRecordService()->findRecordsByInviteUserId($user['id']);
            $payingUserCount = 0;
            $totalPrice = 0;
            $totalCoinAmount = 0;
            $totalAmount = 0;

            foreach ($invitedRecords as $keynum => $invitedRecord) {
                list($coinAmountTotalPrice, $amountTotalPrice, $tempPrice) = $this->getUserOrderDataByUserIdAndTime($invitedRecord['invitedUserId'], $invitedRecord['inviteTime']);

                if ($coinAmountTotalPrice || $amountTotalPrice) {
                    $payingUserCount = $payingUserCount + 1;
                }

                $totalCoinAmount = $totalCoinAmount + $coinAmountTotalPrice;
                $totalAmount = $totalAmount + $amountTotalPrice;
                $totalPrice = $totalPrice + $tempPrice;
            }

            $inviteInformations[] = array(
                'id' => $user['id'],
                'nickname' => $user['nickname'],
                'payingUserCount' => $payingUserCount,
                'payingUserTotalPrice' => $totalPrice,
                'coinAmountPrice' => $totalCoinAmount,
                'amountPrice' => $totalAmount,
                'count' => count($invitedRecords),
            );
        }

        return $this->render('admin/invite/index.html.twig', array(
            'paginator' => $paginator,
            'inviteInformations' => $inviteInformations,
        ));
    }

    public function inviteDetailAction(Request $request)
    {
        $inviteUserId = $request->query->get('inviteUserId');

        $details = array();

        $invitedRecords = $this->getInviteRecordService()->findRecordsByInviteUserId($inviteUserId);

        foreach ($invitedRecords as $key => $invitedRecord) {
            list($coinAmountTotalPrice, $amountTotalPrice, $totalPrice) = $this->getUserOrderDataByUserIdAndTime($invitedRecord['invitedUserId'], $invitedRecord['inviteTime']);

            $user = $this->getUserService()->getUser($invitedRecord['invitedUserId']);

            if (!empty($user)) {
                $details[] = array(
                    'userId' => $user['id'],
                    'nickname' => $user['nickname'],
                    'totalPrice' => $totalPrice,
                    'amountTotalPrice' => $amountTotalPrice,
                    'coinAmountTotalPrice' => $coinAmountTotalPrice,
                );
            }
        }

        return $this->render('admin/invite/invite-modal.html.twig', array(
            'details' => $details,
        ));
    }

    // 得到这个用户在注册后消费情况，订单消费总额；订单虚拟币总额；订单现金总额
    protected function getUserOrderDataByUserIdAndTime($userId, $inviteTime)
    {
        $coinAmountTotalPrice = $this->getOrderService()->analysisCoinAmount(array('userId' => $userId, 'coinAmount' => 0, 'status' => 'paid', 'paidStartTime' => $inviteTime));
        $amountTotalPrice = $this->getOrderService()->analysisAmount(array('userId' => $userId, 'amount' => 0, 'status' => 'paid', 'paidStartTime' => $inviteTime));
        $totalPrice = $this->getOrderService()->analysisTotalPrice(array('userId' => $userId, 'status' => 'paid', 'paidStartTime' => $inviteTime));
        return array($coinAmountTotalPrice, $amountTotalPrice, $totalPrice);
    }

    protected function getAllUsersByRecords($records)
    {
        $inviteUserIds = ArrayToolkit::column($records, 'inviteUserId');
        $invitedUserIds = ArrayToolkit::column($records, 'invitedUserId');
        $userIds = array_merge($inviteUserIds, $invitedUserIds);
        $users = $this->getUserService()->findUsersByIds($userIds);

        return $users;
    }

    public function couponAction(Request $request, $filter)
    {
        $fileds = $request->query->all();
        $conditions = array();
        $conditions = $this->_prepareQueryCondition($fileds);

        if ($filter == 'invite') {
            $conditions['inviteUserCardIdNotEqual'] = 0;
        } elseif ($filter == 'invited') {
            $conditions['invitedUserCardIdNotEqual'] = 0;
        }

        list($paginator, $cardInformations) = $this->getCardInformations($request, $conditions);

        if ($filter == 'invite') {
            $cardIds = ArrayToolkit::column($cardInformations, 'inviteUserCardId');
        } elseif ($filter == 'invited') {
            $cardIds = ArrayToolkit::column($cardInformations, 'invitedUserCardId');
        }

        $cards = $this->getCardService()->findCardsByCardIds($cardIds);
        list($coupons, $orders, $users) = $this->getCardsData($cards);

        return $this->render('admin/invite/coupon.html.twig', array(
            'paginator' => $paginator,
            'cardInformations' => $cardInformations,
            'filter' => $filter,
            'users' => $users,
            'coupons' => $coupons,
            'cards' => $cards,
            'orders' => $orders,
        ));
    }

    public function queryInviteCouponAction(Request $request)
    {
        $fileds = $request->query->all();
        $conditions = array();
        $conditions = $this->_prepareQueryCondition($fileds);
        $conditions['cardType'] = 'coupon';
        $cards = $this->getCardService()->searchCards(
            $conditions,
            array('id' => 'ASC'),
            0,
            PHP_INT_MAX
        );
        $cards = ArrayToolkit::index($cards, 'cardId');
        list($coupons, $orders, $users) = $this->getCardsData($cards);
        $conditions = array();
        $conditions['inviteUserCardIds'] = empty($cards) ? array(-1) : ArrayToolkit::column($cards, 'cardId');
        list($paginator, $cardInformations) = $this->getCardInformations($request, $conditions);

        return $this->render('admin/invite/coupon.html.twig', array(
            'paginator' => $paginator,
            'cardInformations' => $cardInformations,
            'filter' => 'invite',
            'users' => $users,
            'coupons' => $coupons,
            'cards' => $cards,
            'orders' => $orders,
        ));
    }

    private function _prepareQueryCondition($fileds)
    {
        $conditions = array();

        if (!empty($fileds['nickname'])) {
            $conditions['nickname'] = $fileds['nickname'];
        }

        if (!empty($fileds['startDateTime'])) {
            $conditions['startDateTime'] = strtotime($fileds['startDateTime']);
        }

        if (!empty($fileds['endDateTime'])) {
            $conditions['endDateTime'] = strtotime($fileds['endDateTime']);
        }

        return $conditions;
    }

    private function getCardsData($cards)
    {
        $coupons = $this->getCouponService()->findCouponsByIds(ArrayToolkit::column($cards, 'cardId'));

        $orders = $this->getOrderService()->findOrdersByIds(ArrayToolkit::column($coupons, 'orderId'));

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($cards, 'userId'));

        return array($coupons, $orders, $users);
    }

    private function getCardInformations($request, $conditions)
    {
        $paginator = new Paginator(
            $request,
            $this->getInviteRecordService()->countRecords($conditions),
            20
        );

        $cardInformations = $this->getInviteRecordService()->searchRecords(
            $conditions,
            array('inviteTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return array($paginator, $cardInformations);
    }

    protected function getInviteRecordService()
    {
        return $this->createService('User:InviteRecordService');
    }

    protected function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    protected function getCardService()
    {
        return $this->createService('Card:CardService');
    }

    protected function getCouponService()
    {
        return $this->createService('Coupon:CouponService');
    }
}
