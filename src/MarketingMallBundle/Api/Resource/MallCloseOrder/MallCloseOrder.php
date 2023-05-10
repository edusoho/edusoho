<?php

namespace MarketingMallBundle\Api\Resource\MallCloseOrder;

use ApiBundle\Api\ApiRequest;
use Biz\UnifiedPayment\Service\UnifiedPaymentService;
use MarketingMallBundle\Api\Resource\BaseResource;
use MarketingMallBundle\Client\MarketingMallClient;

class MallCloseOrder extends BaseResource
{
    public function add(ApiRequest $request)
    {
        $client = new MarketingMallClient($this->getBiz());
        $params = [
            'sn' => $request->request->get('orderSn'),
        ];
        $trade = $this->getUnifiedPaymentService()->getTradeByOrderSnAndPlatform($params['sn'], 'wechat');
        if ($this->getCurrentUser()->getId() == $trade['userId'] && 'Mall' == $trade['source'] && 'closed' != $trade['status']) {
            $this->getUnifiedPaymentService()->closeTrade($trade['sn']);
            $client->closeOrder($params);
        }

        return [
            'ok' => true,
        ];
    }

    /**
     * @return UnifiedPaymentService
     */
    protected function getUnifiedPaymentService()
    {
        return $this->service('UnifiedPayment:UnifiedPaymentService');
    }
}
