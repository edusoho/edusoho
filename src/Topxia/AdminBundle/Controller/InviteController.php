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

    public function couponListAction(Request $request, $filter)
    {
        $conditions = $request->query->all();

        if (isset($conditions['nickname']) && $conditions['nickname'] == "") {
            unset($conditions['nickname']);
        }

        $conditions['startDateTime'] = !empty($conditions['startDateTime']) ? strtotime($conditions['startDateTime']) : null;
        $conditions['endDateTime']   = !empty($conditions['endDateTime']) ? strtotime($conditions['endDateTime']) : null;
        $cards                       = $this->getCardService()->searchCards(
            $conditions,
            array('useTime', 'DESC'),
            0,
            9999
        );


        $coupons = $this->getCouponService()->findCouponsByIds(ArrayToolkit::column($cards,'cardId'));

        $orders = $this->getOrderService()->findOrdersByIds(ArrayToolkit::column($coupons, 'orderId'));

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($cards, 'userId'));

        if ($filter == 'invite') {
            $map['inviteUserCardIds'] = empty($cards) ? array(-1) : ArrayToolkit::column($cards, 'cardId');
        } else {
            $map['invitedUserCardIds'] = empty($cards) ? array(-1) : ArrayToolkit::column($cards, 'cardId');
        }

        $paginator = new Paginator(

            $this->get('request'),

            $this->getInviteRecordService()->searchRecordCount($map),

            20
        );

        $cardInformations = $this->getInviteRecordService()->searchRecords(
            $map,
            array('id', 'ASC'),

            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()

        );

        $cards   = ArrayToolkit::index($cards, 'cardId');
      
        return $this->render('TopxiaAdminBundle:Invite:coupon.html.twig', array(

            'cardInformations' => $cardInformations,
            'filter'           => $filter,
            'users'            => $users,
            'coupons'          => $coupons,
            'cards'            => $cards,
            'orders'           => $orders
        ));
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
