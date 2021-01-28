<?php

namespace ApiBundle\Api\Resource\Order;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Annotation\Access;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\OrderToolkit;

class OrderLog extends AbstractResource
{
    /**
     * @Access(roles="ROLE_ADMIN,ROLE_SUPER_ADMIN")
     */
    public function search(ApiRequest $request, $orderId)
    {
        $orderLogs = $this->getOrderService()->findOrderLogsByOrderId($orderId);

        $orderLogs = OrderToolkit::removeUnneededLogs($orderLogs);

        $total = count($orderLogs);
        $offset = 0;
        $limit = $total;

        if (!empty($orderLogs)) {
            $this->getOCUtil()->replaceWithObjValue(
                $orderLogs,
                array('user_id' => array('nickname' => 'nickname', 'mobile' => 'verifiedMobile')),
                'user'
            );

            $dicts = $this->container->get('codeages_plugin.dict_twig_extension')->getDict('orderLogStatus');

            foreach ($orderLogs as &$orderLog) {
                $orderLog['statusName'] = empty($dicts[$orderLog['status']]) ? '' : $dicts[$orderLog['status']];
            }
        }

        return $this->makePagingObject($orderLogs, $total, $offset, $limit);
    }

    protected function getOrderService()
    {
        return $this->service('Order:OrderService');
    }
}
