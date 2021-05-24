<?php

namespace Biz\SCRM\Service\Impl;

use Biz\BaseService;
use Biz\SCRM\Service\SCRMTradeService;
use ESCloud\SDK\Service\ScrmService;

class SCRMTradeServiceImpl extends BaseService implements SCRMTradeService
{
    public function verifyOrder($orderId, $token)
    {
        $orderInfo = $this->getSCRMService()->verifyOrder($orderId, $token);
        if (empty($orderInfo['uuid'])) {
        }
    }

    /**
     * @return ScrmService
     */
    protected function getSCRMService()
    {
        $biz = $this->biz;

        return $this->biz['ESCloudSdk.scrm'];
    }
}
