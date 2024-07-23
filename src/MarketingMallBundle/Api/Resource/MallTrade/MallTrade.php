<?php

namespace MarketingMallBundle\Api\Resource\MallTrade;

use ApiBundle\Api\Annotation\AuthClass;
use ApiBundle\Api\ApiRequest;
use AppBundle\Common\ArrayToolkit;
use Biz\Common\CommonException;
use Biz\UnifiedPayment\Service\UnifiedPaymentService;
use MarketingMallBundle\Api\Resource\BaseResource;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MallTrade extends BaseResource
{
    /**
     * @AuthClass(ClassName="MarketingMallBundle\Security\Firewall\MallAuthTokenAuthenticationListener")
     */
    public function add(ApiRequest $request)
    {
        $fields = $request->request->all();
        if (!ArrayToolkit::requireds($fields, ['orderSn', 'title', 'amount', 'userId', 'createIp', 'redirectUrl'], true)) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        $trade = ArrayToolkit::parts($fields, [
            'orderSn',
            'title',
            'amount',
            'userId',
            'openId',
            'description',
            'createIp',
            'redirectUrl',
        ]);

        $trade['description'] = $fields['description'] ?? '';
        $trade['platform'] = 'wechat';
        $trade['platformType'] = 'Js';
        $trade['source'] = 'Mall';
        $trade['notifyUrl'] = $this->generateUrl('unified_payment_notify', ['payment' => 'wechat'], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->getUnifiedPaymentService()->createTrade($trade);
    }

    /**
     * @return UnifiedPaymentService
     */
    protected function getUnifiedPaymentService()
    {
        return $this->service('UnifiedPayment:UnifiedPaymentService');
    }
}
