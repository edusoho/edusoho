<?php

namespace ApiBundle\Api\Resource\UnifiedPayment;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\UnifiedPayment\Service\UnifiedPaymentService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UnifiedPaymentCloseTrade extends AbstractResource
{
    public function add(ApiRequest $request, $tradeSn)
    {
        $trade = $this->getUnifiedPaymentService()->getTradeByTradeSn($tradeSn);
        if (empty($trade)) {
            throw new NotFoundHttpException(sprintf('订单#%s未找到', $tradeSn));
        }
        if ($this->getCurrentUser()->getId() == $trade['userId'] && 'closed' != $trade['status']) {
            $this->getUnifiedPaymentService()->closeTrade($trade['tradeSn']);
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
