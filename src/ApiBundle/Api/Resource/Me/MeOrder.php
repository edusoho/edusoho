<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\Annotation\ResponseFilter;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\OrderFacade\Product\Product;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MeOrder extends AbstractResource
{
    /**
     * @ResponseFilter(class="ApiBundle\Api\Resource\Me\MeOrderFilter", mode="simple")
     */
    public function search(ApiRequest $request)
    {
        $conditions = [
            'user_id' => $this->getCurrentUser()->getId(),
        ];
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $orders = $this->getOrderService()->searchOrders(
            $conditions,
            ['created_time' => 'DESC'],
            $offset,
            $limit
        );

        /*
         * targetType/targetId
         */
        foreach ($orders as &$order) {
            $product = $this->getProduct($order['id']);
            $order['cover'] = empty($product->cover) ? ['middle' => ''] : $product->cover;
            $order['targetType'] = $product->targetType;
            $order['targetId'] = $product->targetId; //targetId要转化成正常的接口
            $order['targetUrl'] = empty($product->successUrl[1]['id']) ? '' : $this->generateUrl($product->successUrl[0], $product->successUrl[1], UrlGeneratorInterface::ABSOLUTE_URL); //跳转URL需要改造
            $order['goodsId'] = $product->goodsId;
            $order['specsId'] = $product->goodsSpecsId;
        }
        $total = $this->getOrderService()->countOrders($conditions);

        return $this->makePagingObject($orders, $total, $offset, $limit);
    }

    /**
     * @param $orderId
     *
     * @return Product
     */
    private function getProduct($orderId)
    {
        $orderItems = $this->getOrderService()->findOrderItemsByOrderId($orderId);
        $orderItem = reset($orderItems);

        return $this->getOrderFacadeService()->getOrderProductByOrderItem($orderItem);
    }

    protected function getOrderService()
    {
        return $this->getBiz()->service('Order:OrderService');
    }

    private function getOrderFacadeService()
    {
        return $this->getBiz()->service('OrderFacade:OrderFacadeService');
    }
}
