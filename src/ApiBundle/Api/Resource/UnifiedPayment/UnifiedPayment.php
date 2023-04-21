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
        $params = $request->request->all();
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

        $order = [
            'title' => '测试商品标题',
            'trade_sn' => md5(uniqid()),
            'amount' => 288800,
        ];

        $trade = $this->createTrade($order, [
            'create_ip' => $request->getHttpRequest()->getClientIp(),
        ]);

        return [
            'config' => $trade,
            'title' => $order['title'],
            'tradeSn' => $order['trade_sn'],
            'amount' => $order['amount'],
            'returnUrl' => $this->generateUrl('cashier_pay_return', ['payment' => 'wechat'], UrlGeneratorInterface::ABSOLUTE_URL),
        ];
    }

    protected function createTrade(array $order, array $params)
    {
        $url = $this->generateUrl('cashier_pay_notify', ['payment' => 'wechat'], UrlGeneratorInterface::ABSOLUTE_URL);
        $trade = [
            'platform_type' => 'Js',
            'goods_title' => '',
            'goods_detail' => '',
            'attach' => [],
            'trade_sn' => $order['tradeSn'],
            'amount' => $order['amount'],
            'notify_url' => $url,
            'open_id' => $order['amount'],
            'create_ip' => $params['createIp'],
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
