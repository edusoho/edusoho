<?php

namespace Topxia\WebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\StringToolkit;
use Topxia\Component\Payment\Payment;
use Topxia\WebBundle\Util\AvatarAlert;
use Symfony\Component\HttpFoundation\Response;
use Topxia\WebBundle\Controller\BaseController;
use Topxia\Common\Paginator;


class CardBagController extends BaseController
{

    public function generateCardAction(Request $request)
    {
        
        $user = $this->getCurrentUser();

        $fields = $request->query->all();  
        $card = array(
            'userId' => $user['id'],
        );
        if ($request->get('cardType') == 'moneyCard'){
            $card['cardType'] = 'moneyCard';
            $card['cardId'] = $fields['cardId'];
            $card['password'] = $fields['password'];
            $card['deadline'] = $fields['deadline'];
            $card['useTime'] = $fields['rechargeTime'];
            $card['status'] = $fields['cardStatus'];
            $card['batchId'] = $fields['batchId'];

        } elseif ($request->get('cardType' == 'coupon')) {
            $card['cardType'] = 'coupon';
            $card['cardId'] = $fields['code'];
            $card['deadline'] = $fields['deadline'];
            $card['useTime'] = $fields['orderTime'];
            $card['status'] = $fields['status'];
            $card['batchId'] = $fields['batchId'];
            $card['targetType'] = $fields['targetType'];
            $card['targetId'] = $fields['targetId'];
            $card['couponType'] = $fields['type'];
            $card['rate'] = $fields['rate'];
        } else {
            $card = $fields;
        }

        $this->getCardBagService()->addCardToCardBag($card);

    }

    // public function indexAction(Request $request)
    // {
    //     $user = $this->getCurrentUser();

    // }

    protected function getCardBagService() {
    	return $this->getServiceKernel()->createService('CardBag.CardBagService');
    }
}
