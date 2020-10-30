<?php

namespace AppBundle\Controller\AdminV2\Marketing;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\Card\Service\CardService;
use Biz\Coupon\Service\CouponService;
use Biz\User\Service\InviteRecordService;
use Codeages\Biz\Order\Service\OrderService;
use Symfony\Component\HttpFoundation\Request;

class InviteController extends BaseController
{
    public function recordAction(Request $request)
    {
        $conditions = $request->query->all();
        $conditions = ArrayToolkit::parts($conditions, ['nickname', 'startDate', 'endDate']);

        $page = $request->query->get('page', 0);
        $firstPage = 1;

        if (!empty($conditions['nickname'])) {
            $user = $this->getUserService()->getUserByNickname($conditions['nickname']);
            $conditions['inviteUserId'] = empty($user) ? '0' : $user['id'];
            unset($conditions['nickname']);

            if (empty($page) || $page == $firstPage) {
                $invitedRecord = $this->getInvitedRecordByUserIdAndConditions($user, $conditions);
            }
        }

        $recordCount = $this->getInviteRecordService()->countRecords($conditions);
        $paginator = new Paginator(
            $this->get('request'),
            $recordCount,
            20
        );

        $inviteRecords = $this->getInviteRecordService()->searchRecords(
            $conditions,
            ['inviteTime' => 'desc'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        if (!empty($invitedRecord)) {
            $inviteRecords = array_merge($invitedRecord, $inviteRecords);
        }

        $users = $this->getInviteRecordService()->getAllUsersByRecords($inviteRecords);

        return $this->render('admin-v2/marketing/invite/records.html.twig', [
            'records' => $inviteRecords,
            'users' => $users,
            'paginator' => $paginator,
        ]);
    }

    public function userRecordsAction(Request $request)
    {
        $conditions = [];
        $nickName = $request->query->get('nickname');
        if (!empty($nickName)) {
            $user = $this->getUserService()->getUserByNickname($nickName);
            $conditions['inviteUserId'] = empty($user) ? '0' : $user['id'];
        }
        $paginator = new Paginator(
            $request,
            $this->getInviteRecordService()->countInviteUser($conditions),
            20
        );

        $records = $this->getInviteRecordService()->searchRecordGroupByInviteUserId(
            $conditions,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('admin-v2/marketing/invite/user-record.html.twig', [
            'paginator' => $paginator,
            'records' => $records,
        ]);
    }

    public function inviteDetailAction(Request $request)
    {
        $inviteUserId = $request->query->get('inviteUserId');

        $invitedRecords = $this->getInviteRecordService()->findRecordsByInviteUserId($inviteUserId);
        $invitedUserIds = ArrayToolkit::column($invitedRecords, 'invitedUserId');

        $users = $this->getUserService()->findUsersByIds($invitedUserIds);

        return $this->render('admin-v2/marketing/invite/invite-modal.html.twig', [
            'invitedRecords' => $invitedRecords,
            'users' => $users,
        ]);
    }

    public function couponAction(Request $request, $filter)
    {
        $fileds = $request->query->all();
        $conditions = [];
        $conditions = $this->_prepareQueryCondition($fileds);

        if ('invite' == $filter) {
            $conditions['inviteUserCardIdNotEqual'] = 0;
        } elseif ('invited' == $filter) {
            $conditions['invitedUserCardIdNotEqual'] = 0;
        }

        list($paginator, $cardInformations) = $this->getCardInformations($request, $conditions);

        if ('invite' == $filter) {
            $cardIds = ArrayToolkit::column($cardInformations, 'inviteUserCardId');
        } elseif ('invited' == $filter) {
            $cardIds = ArrayToolkit::column($cardInformations, 'invitedUserCardId');
        }

        $cards = $this->getCardService()->findCardsByCardIds($cardIds);
        list($coupons, $orders, $users) = $this->getCardsData($cards);

        return $this->render('admin-v2/marketing/invite/coupon.html.twig', [
            'paginator' => $paginator,
            'cardInformations' => $cardInformations,
            'filter' => $filter,
            'users' => $users,
            'coupons' => $coupons,
            'cards' => $cards,
            'orders' => $orders,
        ]);
    }

    public function queryInviteCouponAction(Request $request)
    {
        $fileds = $request->query->all();
        $conditions = [];
        $conditions = $this->_prepareQueryCondition($fileds);
        $conditions['cardType'] = 'coupon';
        $cards = $this->getCardService()->searchCards(
            $conditions,
            ['id' => 'ASC'],
            0,
            PHP_INT_MAX
        );
        $cards = ArrayToolkit::index($cards, 'cardId');
        list($coupons, $orders, $users) = $this->getCardsData($cards);
        $conditions = [];
        $conditions['inviteUserCardIds'] = empty($cards) ? [-1] : ArrayToolkit::column($cards, 'cardId');
        list($paginator, $cardInformations) = $this->getCardInformations($request, $conditions);

        return $this->render('admin-v2/marketing/invite/coupon.html.twig', [
            'paginator' => $paginator,
            'cardInformations' => $cardInformations,
            'filter' => 'invite',
            'users' => $users,
            'coupons' => $coupons,
            'cards' => $cards,
            'orders' => $orders,
        ]);
    }

    private function _prepareQueryCondition($fileds)
    {
        $conditions = [];

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
        $orders = ArrayToolkit::index($orders, 'id');

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($cards, 'userId'));

        return [$coupons, $orders, $users];
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
            ['inviteTime' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return [$paginator, $cardInformations];
    }

    protected function getInvitedRecordByUserIdAndConditions($user, $conditions)
    {
        if (empty($user)) {
            return [];
        }
        $invitedRecordConditions = ArrayToolkit::parts($conditions, ['startDate', 'endDate']);
        $invitedRecordConditions['invitedUserId'] = $user['id'];
        $invitedRecord = $this->getInviteRecordService()->searchRecords(
            $invitedRecordConditions,
            [],
            0,
            1
        );

        return ArrayToolkit::index($invitedRecord, 'id');
    }

    /**
     * @return InviteRecordService
     */
    protected function getInviteRecordService()
    {
        return $this->createService('User:InviteRecordService');
    }

    /**
     * @return CardService
     */
    protected function getCardService()
    {
        return $this->createService('Card:CardService');
    }

    /**
     * @return CouponService
     */
    protected function getCouponService()
    {
        return $this->createService('Coupon:CouponService');
    }

    /**
     * @return OrderService
     */
    protected function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }
}
