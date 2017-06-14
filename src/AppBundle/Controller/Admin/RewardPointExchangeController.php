<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use Symfony\Component\HttpFoundation\Request;

class RewardPointExchangeController extends BaseController
{
    public function indexAction(Request $request)
    {
        $fields = $request->query->all();

        $conditions = array(
            'keywordType' => '',
            'keywordStatus' => '',
            'keyword' => '',
        );

        $conditions = array_merge($conditions, $fields);

        $paginator = new Paginator(
            $request,
            $this->getRewardPointProductOrderService()->countProductOrders($conditions),
            20
        );

        $orders = $this->getRewardPointProductOrderService()->searchProductOrders(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $productIds = ArrayToolkit::column($orders, 'productId');
        $products = $this->getRewardPointProductService()->findProductsByIds($productIds);

        $userIds = ArrayToolkit::column($orders, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render(
            'admin/reward-point-mall/exchange/index.html.twig',
            array(
                'orders' => $orders,
                'paginator' => $paginator,
                'products' => ArrayToolkit::index($products, 'id'),
                'users' => ArrayToolkit::index($users, 'id'),
            )
        );
    }

    public function deliverAction(Request $request, $id)
    {
        $order = $this->getRewardPointProductOrderService()->getProductOrder($id);

        if ($request->getMethod() == 'POST') {
            $fields = $request->request->all();

            $order = $this->getRewardPointProductOrderService()->deliverProduct($id, $fields);

            $product = $this->getRewardPointProductService()->getProduct($order['productId']);

            $user = $this->getUserService()->getUser($order['userId']);

            return $this->render('admin/reward-point-mall/exchange/list-tr.html.twig',
                array(
                    'order' => $order,
                    'product' => $product,
                    'user' => $user,
                )
            );
        }

        return $this->render('admin/reward-point-mall/exchange/modal.html.twig',
            array(
                'order' => $order,
            )
        );
    }

    public function updateMessageAction(Request $request, $id)
    {
        $order = $this->getRewardPointProductOrderService()->getProductOrder($id);

        if ($request->getMethod() == 'POST') {
            $fields = $request->request->all();

            $order = $this->getRewardPointProductOrderService()->updateProductOrder($id, $fields);

            $product = $this->getRewardPointProductService()->getProduct($order['productId']);

            $user = $this->getUserService()->getUser($order['userId']);

            return $this->render('admin/reward-point-mall/exchange/list-tr.html.twig',
                array(
                    'order' => $order,
                    'product' => $product,
                    'user' => $user,
                )
            );
        }

        return $this->render(
            'admin/reward-point-mall/exchange/modal.html.twig',
            array(
                'order' => $order,
            )
        );
    }

    /**
     * @return RewardPointProductService
     */
    protected function getRewardPointProductService()
    {
        return $this->createService('RewardPoint:ProductService');
    }

    /**
     * @return RewardPointProductOrderService
     */
    protected function getRewardPointProductOrderService()
    {
        return $this->createService('RewardPoint:ProductOrderService');
    }
}
