<?php

namespace AppBundle\Controller\Goods;

use AppBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;

class GoodsController extends BaseController
{
    public function showAction(Request $request, $id)
    {
        return $this->render(
            'goods/show.html.twig',
            []
        );
    }

    public function minScreenShowAction(Request $request, $id)
    {
        return $this->render('goods/min-screen-show.html.twig', []);
    }
}
