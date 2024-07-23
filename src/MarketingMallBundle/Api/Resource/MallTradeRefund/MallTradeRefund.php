<?php

namespace MarketingMallBundle\Api\Resource\MallTradeRefund;

use ApiBundle\Api\Annotation\AuthClass;
use ApiBundle\Api\ApiRequest;
use AppBundle\Common\ArrayToolkit;
use Biz\Common\CommonException;
use Biz\UnifiedPayment\Service\UnifiedPaymentService;
use MarketingMallBundle\Api\Resource\BaseResource;

class MallTradeRefund extends BaseResource
{
    /**
     * @AuthClass(ClassName="MarketingMallBundle\Security\Firewall\MallAuthTokenAuthenticationListener")
     */
    public function add(ApiRequest $request)
    {
        $fields = $request->request->all();
        if (!ArrayToolkit::requireds($fields, ['tradeSn', 'refundAmount'], true)) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        $trade = [];
        $trade['tradeSn'] = $fields['tradeSn'] ?? '';
        $trade['refundAmount'] = $fields['refundAmount'] ?? '';

        return $this->getUnifiedPaymentService()->refund($trade);
    }

    /**
     * @return UnifiedPaymentService
     */
    protected function getUnifiedPaymentService()
    {
        return $this->service('UnifiedPayment:UnifiedPaymentService');
    }
}
