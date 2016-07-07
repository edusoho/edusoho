<?php
namespace Topxia\AdminBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class InviteController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = $request->query->all();
        $conditions = ArrayToolkit::parts($conditions, array('nickname'));
        $paginator  = new Paginator(
            $this->get('request'),
            $this->getUserService()->searchUserCount($conditions),
            20
        );

        $users = $this->getUserService()->searchUsers(
            $conditions,
            array('id', 'ASC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $inviteInformations = array();

        foreach ($users as $key => $user) {
            $invitedRecords       = $this->getInviteRecordService()->findRecordsByInviteUserId($user['id']);
            $payingUserCount      = 0;
            $coinAmountTotalPrice = 0;
            $amountTotalPrice     = 0;
            $totalPrice           = 0;
            $totalCoinAmount      = 0;
            $totalAmount          = 0;

            foreach ($invitedRecords as $keynum => $invitedRecord) {
                $coinAmountTotalPrice = $this->getOrderService()->analysisCoinAmount(array('userId' => $invitedRecord['invitedUserId'], 'coinAmount' => 0, 'status' => 'paid', 'paidStartTime' => $invitedRecord['inviteTime']));
                $amountTotalPrice     = $this->getOrderService()->analysisAmount(array('userId' => $invitedRecord['invitedUserId'], 'amount' => 0, 'status' => 'paid', 'paidStartTime' => $invitedRecord['inviteTime']));
                $tempPrice            = $this->getOrderService()->analysisTotalPrice(array('userId' => $invitedRecord['invitedUserId'], 'status' => 'paid', 'paidStartTime' => $invitedRecord['inviteTime']));

                if ($coinAmountTotalPrice || $amountTotalPrice) {
                    $payingUserCount = $payingUserCount + 1;
                }

                $totalCoinAmount = $totalCoinAmount + $coinAmountTotalPrice;
                $totalAmount     = $totalAmount + $amountTotalPrice;
                $totalPrice      = $totalPrice + $tempPrice;
            }

            $inviteInformations[] = array(
                'id'                   => $user['id'],
                'nickname'             => $user['nickname'],
                'payingUserCount'      => $payingUserCount,
                'payingUserTotalPrice' => $totalPrice,
                'coinAmountPrice'      => $totalCoinAmount,
                'amountPrice'          => $totalAmount,
                'count'                => count($invitedRecords)
            );
        }

        return $this->render('TopxiaAdminBundle:Invite:index.html.twig', array(
            'paginator'          => $paginator,
            'inviteInformations' => $inviteInformations
        ));
    }

    public function inviteDetailAction(Request $request)
    {
        $inviteUserId = $request->query->get('inviteUserId');

        $details = array();

        $invitedRecords = $this->getInviteRecordService()->findRecordsByInviteUserId($inviteUserId);

        foreach ($invitedRecords as $key => $invitedRecord) {
            $coinAmountTotalPrice = $this->getOrderService()->analysisCoinAmount(array('userId' => $invitedRecord['invitedUserId'], 'coinAmount' => 0, 'paidStartTime' => $invitedRecord['inviteTime'], 'status' => 'paid'));
            $amountTotalPrice     = $this->getOrderService()->analysisAmount(array('userId' => $invitedRecord['invitedUserId'], 'amount' => 0, 'paidStartTime' => $invitedRecord['inviteTime'], 'status' => 'paid'));
            $totalPrice           = $this->getOrderService()->analysisTotalPrice(array('userId' => $invitedRecord['invitedUserId'], 'status' => 'paid', 'paidStartTime' => $invitedRecord['inviteTime']));
            $user                 = $this->getUserService()->getUser($invitedRecord['invitedUserId']);

            if (!empty($user)) {
                $details[] = array(
                    'userId'               => $user['id'],
                    'nickname'             => $user['nickname'],
                    'totalPrice'           => $totalPrice,
                    'amountTotalPrice'     => $amountTotalPrice,
                    'coinAmountTotalPrice' => $coinAmountTotalPrice
                );
            }
        }

        return $this->render('TopxiaAdminBundle:Invite:invite-modal.html.twig', array(
            'details' => $details
        ));
    }

    public function couponAction(Request $request, $filter)
    {
        $fileds     = $request->query->all();
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

        $cards                          = $this->getCardService()->findCardsByCardIds($cardIds);
        list($coupons, $orders, $users) = $this->getCardsData($cards);
        return $this->render('TopxiaAdminBundle:Invite:coupon.html.twig', array(
            'paginator'        => $paginator,
            'cardInformations' => $cardInformations,
            'filter'           => $filter,
            'users'            => $users,
            'coupons'          => $coupons,
            'cards'            => $cards,
            'orders'           => $orders
        ));
    }

    public function queryInviteCouponAction(Request $request)
    {
        $fileds                 = $request->query->all();
        $conditions             = array();
        $conditions             = $this->_prepareQueryCondition($fileds);
        $conditions['cardType'] = 'coupon';
        $cards                  = $this->getCardService()->searchCards(
            $conditions,
            array('id', 'ASC'),
            0,
            PHP_INT_MAX
        );
        $cards                              = ArrayToolkit::index($cards, 'cardId');
        list($coupons, $orders, $users)     = $this->getCardsData($cards);
        $conditions                         = array();
        $conditions['inviteUserCardIds']    = empty($cards) ? array(-1) : ArrayToolkit::column($cards, 'cardId');
        list($paginator, $cardInformations) = $this->getCardInformations($request, $conditions);
        return $this->render('TopxiaAdminBundle:Invite:coupon.html.twig', array(
            'paginator'        => $paginator,
            'cardInformations' => $cardInformations,
            'filter'           => 'invite',
            'users'            => $users,
            'coupons'          => $coupons,
            'cards'            => $cards,
            'orders'           => $orders
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
            $this->getInviteRecordService()->searchRecordCount($conditions),
            20
        );

        $cardInformations = $this->getInviteRecordService()->searchRecords(
            $conditions,
            array('inviteTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return array($paginator, $cardInformations);
    }

    protected function getInviteRecordService()
    {
        return $this->getServiceKernel()->createService('User.InviteRecordService');
    }

    protected function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order.OrderService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getCardService()
    {
        return $this->getServiceKernel()->createService('Card.CardService');
    }

    protected function getCouponService()
    {
        return $this->getServiceKernel()->createService('Coupon.CouponService');
    }
}
