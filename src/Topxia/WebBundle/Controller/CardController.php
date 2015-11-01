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

        $cards = $this->getCardService()->findCardsByUserIdAndCardType($user['id'],$cardType);
        $cardIds = ArrayToolkit::column($cards,'cardId');
        $cards = $this->sortCards($cards);
        $filter = $request->query->get('filter');
        if (!empty($filter)) {
            $groupCards = ArrayToolkit::group($cards ,'status');
            if ($filter == 'useable') {
                $cards = isset($groupCards['useable']) ? $groupCards['useable'] : null ;
            } elseif ($filter == 'used') {
                $cards = isset($groupCards['used']) ? $groupCards['used'] : null ;
            } elseif ($filter == 'outdate') {
                $cards = isset($groupCards['outdate']) ? $groupCards['outdate'] : null ;
            } elseif ($filter == 'invalid') {
                $cards = isset($groupCards['invalid']) ? $groupCards['invalid'] : null ;
            }
        }
        
        $cardsDetail = $this->getCardService()->findCardDetailsByCardTypeAndCardIds($cardType,$cardIds);
        return $this->render('TopxiaWebBundle:Card:index.html.twig',array(
            'cards' => $cards,
            'cardDetails' => ArrayToolkit::index($cardsDetail,'id')
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

    protected function sortCards($cards)
    {
        $cards = $this->getCardService()->sortArrayByfield($cards,'deadline');
        $cards = ArrayToolkit::group($cards, 'status');
        $sortedCards = array();

        $currentTime = time();
        $usedCards = isset($cards['used']) ? $cards['used'] : array();
        $invalidCards = isset($cards['invalid']) ? $cards['invalid'] : array();
        $outDateCards = array();
        $receiveCards = array();
        if (isset($cards['receive'])){
            foreach ($cards['receive'] as $card) {
                if($card['deadline'] < $currentTime) {
                    $card['status'] = 'outdate';
                    $outDateCards[] = $card;
                } else {
                    $card['status'] = 'useable';
                    $receiveCards[] = $card;
                }
            }
        }
            

        $sortedCards = array_merge($receiveCards, $usedCards, $outDateCards, $invalidCards);

        return $sortedCards;
       
    }

    protected function getCardService() {
    	return $this->getServiceKernel()->createService('Card.CardService');
    }
}
