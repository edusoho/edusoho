<?php

namespace AppBundle\Controller\PayCenter;

use AppBundle\Controller\BaseController;
use Biz\Order\Service\OrderService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PayCenterController extends BaseController
{
    public function showAction(Request $request)
    {
        $sn = $request->query->get('sn');

        $order = $this->getOrderService()->getOrderBySn($sn);

        if (!$order) {
            throw new NotFoundHttpException();
        }

        return $this->render('pay-center/show.html.twig', array(
            'order' => $order,
        ));
    }

    /**
     * @return OrderService
     */
    private function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }

}
