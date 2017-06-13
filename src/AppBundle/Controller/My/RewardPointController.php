<?php

namespace AppBundle\Controller\My;

use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\Paginator;
use AppBundle\Controller\BaseController;

class RewardPointController extends BaseController
{
    public function indexAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $conditions['userId'] = $user['id'];
        $accountFlowCount = $this->getAccountFlowService()->countAccountFlows($conditions);
        $paginator = new Paginator(
            $request,
            $accountFlowCount,
            10
        );

        $accountFlows = $this->getAccountFlowService()->searchAccountFlows(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('reward-point/index.html.twig', array(
            'accountFlows' => $accountFlows,
            'paginator' => $paginator,
            ));
    }

    public function mallAction(Request $request)
    {
        return $this->render('reward-point/mall.html.twig', array(
        ));
    }

    public function exchangeAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $conditions['userId'] = $user['id'];
        $productOrderCount = $this->getProductOrderService()->countProductOrders($conditions);
        $paginator = new Paginator(
            $request,
            $productOrderCount,
            10
        );

        $productOrders = $this->getProductOrderService()->searchProductOrders(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render(
            'reward-point/exchange.html.twig',
            array(
                'productOrders' => $productOrders,
                'paginator' => $paginator,
            )
        );
    }

    protected function getAccountFlowService()
    {
        return $this->createService('RewardPoint:AccountFlowService');
    }

    protected function getProductOrderService()
    {
        return $this->createService('RewardPoint:ProductOrderService');
    }
}
