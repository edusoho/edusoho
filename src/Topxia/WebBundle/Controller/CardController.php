<?php

namespace Topxia\WebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\StringToolkit;
use Symfony\Component\HttpFoundation\Response;
use Topxia\WebBundle\Controller\BaseController;
use Topxia\Common\Paginator;


class CardController extends BaseController
{

    public function indexAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $cardType = $request->query->get('cardType');
        if(empty($cardType)) {
            $cardType = "coupon";
        }

        if(!$user->isLogin()) {
            return $this->createMessageResponse('error', '用户未登录，请先登录！');
        }

        $cardLists = $this->getCardService()->findCardsByUserIdAndCardType($user['id'],$cardType);
        $cardIds = ArrayToolkit::column($cardLists,'cardId');

        $cardsDetail = $this->getCardService()->findCardsByCardTypeAndCardIds($cardIds,$cardType);
        return $this->render('TopxiaWebBundle:Card:index.html.twig',array(
            'cards' => $cardsDetail
        ));
    	
        
    }

    public function showUsableCardsAction($targetType,$targetId,$totalPrice,$priceType)
    {
        $user = $this->getCurrentUser();
        $cardLists = $this->getCardService()->findCardsByUserIdAndCardType($user['id'],'coupon');
        $cardIds = ArrayToolkit::column($cardLists,'cardId');
        var_dump($cardIds);
        $cardsDetail = $this->getCardService->findCardsByCardTypeAndCardIds($cardIds,'coupon');
        var_dump($cardsDetail);
        return $this->render('TopxiaWebBundle:Order:order-item-coupon.html.twig',array(
            'targetType' => $targetType,
            'targetId' => $targetId,
            'totalPrice' => $totalPrice,
            'priceType' => $priceType
            ));
    }



    protected function getCardService() {
    	return $this->getServiceKernel()->createService('Card.CardService');
    }
}
