<?php

namespace ApiBundle\Api\Resource\Order;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Common\CommonException;
use Biz\Goods\GoodsEntityFactory;
use Biz\Goods\Service\GoodsService;
use Biz\Order\OrderException;
use Biz\OrderFacade\Exception\OrderPayCheckException;
use Biz\OrderFacade\Product\Product;
use Biz\OrderFacade\Service\OrderFacadeService;
use Biz\Product\Service\ProductService;
use Biz\User\Service\UserService;
use Biz\User\UserException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Order extends AbstractResource
{
    public function add(ApiRequest $request)
    {
        $params = $request->request->all();

        if (empty($params['targetId'])
            || empty($params['targetType'])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        $this->filterParams($params);
        $this->convertOrderParams($params);

        try {
            /* @var $product Product */
            $product = $this->getOrderFacadeService()->getOrderProduct($params['targetType'], $params);
            $product->setPickedDeduct($params);

            $this->addCreateDealers($request);

            $order = $this->getOrderFacadeService()->create($product);

            if (!empty($params['isOrderCreate'])) {
                return $order;
            }
            // 优惠券全额抵扣
            if ($this->getOrderFacadeService()->isOrderPaid($order['id'])) {
                return [
                    'id' => $order['id'],
                    'sn' => $order['sn'],
                ];
            } else {
                $this->handleParams($params, $order);
                $apiRequest = new ApiRequest('/api/trades', 'POST', [], $params);
                $trade = $this->invokeResource($apiRequest);

                return [
                    'id' => $trade['tradeSn'],
                    'sn' => $trade['tradeSn'],
                ];
            }
        } catch (OrderPayCheckException $payCheckException) {
            throw new BadRequestHttpException($payCheckException->getMessage(), $payCheckException, $payCheckException->getCode());
        }
    }

    public function get(ApiRequest $request, $sn)
    {
        $order = $this->getOrderService()->getOrderBySn($sn);
        if (!$order) {
            throw OrderException::NOTFOUND_ORDER();
        }
        $paymentTrade = $this->getPayService()->getTradeByTradeSn($order['trade_sn']);
        $order['platform_sn'] = $paymentTrade['platform_sn'];
        $userId = $this->getCurrentUser()->getId();
        if ($this->getCurrentUser()->isAdmin()) {
            return $order;
        } elseif ($userId == $order['user_id']) {
            return $order;
        }
    }

    public function search(ApiRequest $request)
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser->isSuperAdmin()) {
            throw UserException::PERMISSION_DENIED();
        }
        $conditions = $request->query->all();
        $conditions = $this->prepareConditions($conditions);
        list($offset, $limit) = $this->getOffsetAndLimit($request);

        $orders = $this->getOrderService()->searchOrders(
            $conditions,
            ['created_time' => 'DESC'],
            $offset,
            $limit
        );

        $orderIds = ArrayToolkit::column($orders, 'id');
        $orderSns = ArrayToolkit::column($orders, 'sn');

        $orderItems = $this->getOrderService()->findOrderItemsByOrderIds($orderIds);
        $orderItems = ArrayToolkit::index($orderItems, 'order_id');
        $goodsSpecsIds = ArrayToolkit::column($orderItems, 'target_id');
        $goodsSpecs = $this->getGoodsService()->findGoodsSpecsByIds($goodsSpecsIds);
        $goodsSpecs = ArrayToolkit::index($goodsSpecs, 'id');
        foreach ($orderItems as &$orderItem) {
            $orderItem['goodsSpecs'] = $goodsSpecs[$orderItem['target_id']];
        }
        $paymentTrades = $this->getPayService()->findTradesByOrderSns($orderSns);
        $paymentTrades = ArrayToolkit::index($paymentTrades, 'order_sn');
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($orders, 'user_id'));
        $users = ArrayToolkit::index($users, 'id');
        foreach ($orders as &$order) {
            $order['item'] = empty($orderItems[$order['id']]) ? [] : $orderItems[$order['id']];
            $order['trade'] = empty($paymentTrades[$order['sn']]) ? [] : $paymentTrades[$order['sn']];
            $order['user'] = empty($users) ? [] : $users[$order['userid']];
        }

        $total = $this->getOrderService()->countOrders($conditions);

        return $this->makePagingObject($orders, $total, $offset, $limit);
    }

    protected function prepareConditions($conditions)
    {
        if (!empty($conditions['orderItemType'])) {
            $conditions['order_item_target_type'] = $conditions['orderItemType'];
        }

        if (isset($conditions['keywordType'])) {
            $conditions[$conditions['keywordType']] = trim($conditions['keyword']);
        }

        if (!empty($conditions['startDateTime'])) {
            $conditions['start_time'] = strtotime($conditions['startDateTime']);
        }

        if (!empty($conditions['endDateTime'])) {
            $conditions['end_time'] = strtotime($conditions['endDateTime']);
        }

        if (isset($conditions['buyer'])) {
            $user = $this->getUserService()->getUserByNickname($conditions['buyer']);
            $conditions['user_id'] = $user ? $user['id'] : -1;
        }

        if (isset($conditions['mobile'])) {
            $user = $this->getUserService()->getUserByVerifiedMobile($conditions['mobile']);
            $conditions['user_id'] = $user ? $user['id'] : -1;
        }

        if (!empty($conditions['displayStatus'])) {
            $conditions['statuses'] = $this->container->get('web.twig.order_extension')->getOrderStatusFromDisplayStatus($conditions['displayStatus'], 1);
        }
        unset($conditions['page']);

        return $conditions;
    }

    public function filterParams(&$params)
    {
        if (isset($params['coinPayAmount'])) {
            $params['coinAmount'] = $params['coinPayAmount'];
            unset($params['coinPayAmount']);
        }

        if (isset($params['payPassword'])) {
            $params['payPassword'] = $this->decrypt($params['payPassword']);
        }

        if (isset($params['unencryptedPayPassword'])) {
            $params['payPassword'] = $params['unencryptedPayPassword'];
        }
    }

    public function handleParams(&$params, $order)
    {
        $params['gateway'] = (!empty($params['payment']) && 'wechat' == $params['payment']) ? 'WechatPay_MWeb' : 'Alipay_LegacyWap';
        $params['type'] = 'purchase';
        $params['app_pay'] = isset($params['appPay']) && 'Y' == $params['appPay'] ? 'Y' : 'N';
        $params['orderSn'] = $order['sn'];
        if (isset($params['payPassword'])) {
            $params['unencryptedPayPassword'] = $params['payPassword'];
        }
        if ('Alipay_LegacyWap' == $params['gateway']) {
            $params['return_url'] = $this->generateUrl('cashier_pay_return_for_app', ['payment' => 'alipay'], UrlGeneratorInterface::ABSOLUTE_URL);
            $params['show_url'] = $this->generateUrl('cashier_pay_return_for_app', ['payment' => 'alipay'], UrlGeneratorInterface::ABSOLUTE_URL);
        }
    }

    private function addCreateDealers($request)
    {
        $serviceNames = ['Distributor:DistributorProductDealerService', 'S2B2C:S2B2CProductDealerService'];

        foreach ($serviceNames as $serviceName) {
            $service = $this->getBiz()->service($serviceName);
            $service->setParams($request->getHttpRequest()->cookies->all());
            $this->getOrderFacadeService()->addDealer($service);
        }
    }

    private function decrypt($payPassword)
    {
        return \XXTEA::decrypt(base64_decode($payPassword), 'EduSoho');
    }

    /**
     * @return OrderService
     */
    protected function getOrderService()
    {
        return $this->getBiz()->service('Order:OrderService');
    }

    /**
     * @return OrderFacadeService
     */
    private function getOrderFacadeService()
    {
        return $this->service('OrderFacade:OrderFacadeService');
    }

    /**
     * @return PayService
     */
    protected function getPayService()
    {
        return $this->service('Pay:PayService');
    }

    /**
     * @return GoodsService
     */
    private function getGoodsService()
    {
        return $this->service('Goods:GoodsService');
    }

    /**
     * @return ProductService
     */
    private function getProductService()
    {
        return $this->service('Product:ProductService');
    }

    /**
     * @return GoodsEntityFactory
     */
    protected function getGoodsEntityFactory()
    {
        $biz = $this->getBiz();

        return $biz['goods.entity.factory'];
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return $this->service('User:UserService');
    }

    private function convertOrderParams(&$params)
    {
        //goodsSpecs
        if ('goodsSpecs' === $params['targetType']) {
            $specs = $this->getGoodsService()->getGoodsSpecs($params['targetId']);
            $goods = $this->getGoodsService()->getGoods($specs['goodsId']);
            $params['targetType'] = $goods['type'];

            return;
        }
        if (in_array($params['targetType'], ['classroom', 'course'])) {
            $specs = $this->getGoodsEntityFactory()->create($params['targetType'])->getSpecsByTargetId($params['targetId']);
            $params['targetId'] = $specs['id'];

            return;
        }
    }
}
