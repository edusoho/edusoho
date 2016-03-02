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
}
