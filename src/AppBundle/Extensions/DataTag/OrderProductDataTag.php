<?php

namespace AppBundle\Extensions\DataTag;

use Biz\OrderFacade\Service\OrderFacadeService;

class OrderProductDataTag extends BaseDataTag implements DataTag
{
    public function getData(array $arguments)
    {
        if (empty($arguments['orderItem'])) {
            return array();
        }
        try {
            return $this->getOrderFacadeService()->getOrderProductByOrderItem($arguments['orderItem']);
        } catch (\RuntimeException $e) {
            return array();
        }
    }

    /**
     * @return OrderFacadeService
     */
    private function getOrderFacadeService()
    {
        return $this->createService('OrderFacade:OrderFacadeService');
    }
}
