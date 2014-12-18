<?php
namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Topxia\WebBundle\Controller\BaseController;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class CartsController extends BaseController
{
    public function showAction(Request $request)
    {
        # code...
    }

    public function listAction(Request $request)
    {
        // $carts = $this->getCartsService()->searchCarts();
        return $this->render('CustomWebBundle:Carts:list.html.twig',array(
            // 'carts' => $carts
        ));
    }

    private function getCartsService()
    {
        return $this->getServiceKernel()->createService('Carts:CartsService');
    }
}