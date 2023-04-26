<?php

namespace ApiBundle\Api\Resource\UnifiedPayment;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\Exception\InvalidArgumentException;
use Biz\Common\CommonException;
use Biz\UnifiedPayment\Service\UnifiedPaymentService;
use Codeages\Biz\Pay\Payment\WechatGateway;
use Firebase\JWT\JWT;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UnifiedPayment extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function add(ApiRequest $request)
    {
        $params = $request->request->all();
        if (empty($params['token'])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }
        $storage = $this->getSettingService()->get('storage', []);
        if (empty($storage['cloud_secret_key'])) {
            throw new InvalidArgumentException('请配置授权码');
        }

        $payload = (array) JWT::decode($params['token'], $storage['cloud_secret_key'], ['HS256']);
        if (empty($payload)) {
            throw new InvalidArgumentException('令牌内容错误');
        }

        if (empty($payload['tradeSn'])) {
            throw new InvalidArgumentException('令牌内容订单信息缺失：'.json_encode($payload));
        }

        $trade = $this->getUnifiedPaymentService()->getTradeByTradeSn($payload['tradeSn']);
        if (empty($trade) || 'paid' === $trade['status']) {
            return ['status' => 'ok', 'paid' => true, 'message' => '', 'returnUrl' => ''];
        }
        $config = $this->getUnifiedPaymentService()->createPlatformTradeByTradeSn($payload['tradeSn']);

        return [
            'config' => $config,
            'orderSn' => $trade['orderSn'],
            'amount' => $trade['amount'],
            'redirectUrl' => $trade['redirectUrl'],
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
