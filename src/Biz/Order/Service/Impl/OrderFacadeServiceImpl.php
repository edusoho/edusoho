<?php

namespace Biz\Order\Service\Impl;

use Biz\BaseService;
use Biz\Order\OrderProcessor\OrderProcessorFactory;
use Biz\Order\Service\OrderFacadeService;
use AppBundle\Common\JoinPointToolkit;

class OrderFacadeServiceImpl extends BaseService implements OrderFacadeService
{
    public function getOrderInfo($targetType, $targetId, $fields)
    {
        $orderTypes = JoinPointToolkit::load('order');
        if (empty($targetType)
            || empty($targetId)
            || !array_key_exists($targetType, $orderTypes)
        ) {
            throw $this->createServiceException('参数不正确');
        }

        $currentUser = $this->getCurrentUser();
        $processor = OrderProcessorFactory::create($targetType);
        $checkInfo = $processor->preCheck($targetId, $currentUser['id']);

        if (isset($checkInfo['error'])) {
            return array($checkInfo, null, null);
        }

        $orderInfo = $processor->getOrderInfo($targetId, $fields);

        $verifiedMobile = '';

        if ((isset($currentUser['verifiedMobile'])) && (strlen($currentUser['verifiedMobile']) > 0)) {
            $verifiedMobile = $currentUser['verifiedMobile'];
        }

        $orderInfo['verifiedMobile'] = $verifiedMobile;
        $orderInfo['hasPassword'] = strlen($currentUser['password']) > 0;

        return array(null, $orderInfo, $processor);
    }

    public function createOrder($targetType, $targetId, $params)
    {

    }
}