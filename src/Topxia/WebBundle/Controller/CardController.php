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


    // public function indexAction(Request $request)
    // {
    //     $user = $this->getCurrentUser();

    // }

    protected function getCardService() {
    	return $this->getServiceKernel()->createService('Card.CardService');
    }
}
