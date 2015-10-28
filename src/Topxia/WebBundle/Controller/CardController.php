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

    protected function getCardService() {
    	return $this->getServiceKernel()->createService('Card.CardService');
    }
}
