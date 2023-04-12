<?php

namespace ApiBundle\Api\Resource\UnifiedPayment;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\Exception\InvalidArgumentException;
use Biz\Common\CommonException;
use Codeages\Biz\Pay\Payment\WechatGateway;
use Codeages\Biz\Pay\Service\PayService;
use Firebase\JWT\JWT;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UnifiedPayment extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function add(ApiRequest $request)
    {
        $params = $request->query->all();
        if (empty($params['token'])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }
        $storage = $this->getSettingService()->get('storage', []);
        if (empty($storage['cloud_secret_key'])) {
            throw new InvalidArgumentException('请配置授权码');
        }

        $payload =  (array)JWT::decode($params['token'], $storage['cloud_secret_key'], ['HS256']);
        if (empty($payload)) {
            throw new InvalidArgumentException('令牌内容错误');
        }

        $trade = $this->createTrade($payload, [
            'create_ip' => $request->getHttpRequest()->getClientIp()
        ]);

        return [
            'config' => $trade,
            'title' => $payload['title'],
            'tradeSn' => $payload['trade_sn'],
            'amount' => $payload['amount'],
            'returnUrl' => $this->generateUrl('cashier_pay_return', ['payment' => 'wechat'], UrlGeneratorInterface::ABSOLUTE_URL),
        ];
    }

    public function createTrade(array $payload, array $params)
    {
        $url = $this->generateUrl('cashier_pay_notify', ['payment' => 'wechat'], UrlGeneratorInterface::ABSOLUTE_URL);
        $trade = [
            'platform_type' => 'Js',
            'goods_title' => $payload['title'],
            'goods_detail' => '',
            'attach' => [],
            'trade_sn' => $payload['trade_sn'],
            'amount' => $payload['amount'],
            'notify_url' => $url,
            'open_id' => 'openid',
            'create_ip' => $params['create_ip'],
        ];

        return $this->getPayment()->createTrade($trade);
    }

    /**
     * @return WechatGateway
     */
    protected function getPayment()
    {
        return $this->biz['payment.wechat'];
    }

    /**
     * @return PayService
     */
    private function getPayService()
    {
        return $this->service('Pay:PayService');
    }

}
