<?php

namespace Topxia\WebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\StringToolkit;
use Symfony\Component\HttpFoundation\Response;
use Topxia\WebBundle\Controller\BaseController;
use Topxia\Common\Paginator;


class CardBagController extends BaseController
{


    // public function indexAction(Request $request)
    // {
    //     $user = $this->getCurrentUser();

    // }
    public function showCards()
    {
    	
    }

    protected function getCardBagService() {
    	return $this->getServiceKernel()->createService('CardBag.CardBagService');
    }
}
