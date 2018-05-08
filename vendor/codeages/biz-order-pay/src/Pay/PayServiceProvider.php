<?php

namespace Codeages\Biz\Pay;

use Codeages\Biz\Pay\Status\PayTradeContext;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Codeages\Biz\Pay\Payment\SignatureToolkit;

class PayServiceProvider implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        $biz['migration.directories'][] = dirname(dirname(__DIR__)).'/migrations/pay';
        $biz['autoload.aliases']['Pay'] = 'Codeages\Biz\Pay';

        $biz['payment.options'] = null;

        $biz['payment.final_options'] = function () use ($biz) {
            $options = array(
                'closed_by_notify' => false,
                'refunded_by_notify' => false,
                'coin_rate' => 1,
                'goods_title' => '',
            );

            if (!empty($biz['payment.options'])) {
                $options = array_merge($options, $biz['payment.options']);
            }

            return $options;
        };

        $biz['console.commands'][] = function () use ($biz) {
            return new \Codeages\Biz\Pay\Command\TableCommand($biz);
        };

        $this->registerStatus($biz);
        $this->registerPayments($biz);
    }

    protected function registerPayments($biz)
    {
        $paymentDefaultPlatforms = array(
            'wechat' => array(
                'class' => '\Codeages\Biz\Pay\Payment\WechatGateway',
                'appid' => '',
                'mch_id' => '',
                'key' => '',
                'cert_path' => '',
                'key_path' => '',
            ),
            'alipay' => array(
                'class' => '\Codeages\Biz\Pay\Payment\AlipayGateway',
                'seller_email' => '',
                'partner' => '',
                'key' => '',
            ),
            'iap' => array(
                'class' => '\Codeages\Biz\Pay\Payment\IapGateway',
            ),
            'lianlianpay' => array(
                'class' => '\Codeages\Biz\Pay\Payment\LianlianPayGateway',
                'secret' => '',
                'oid_partner' => '',
                'signatureToolkit' => new SignatureToolkit(),
            ),
            'wechat_app' => array(
                'class' => '\Codeages\Biz\Pay\Payment\WeChatAppGateway',
                'appid' => '',
                'mch_id' => '',
                'key' => '',
                'cert_path' => '',
                'key_path' => '',
            ),
        );

        $biz['payment.platforms.options'] = null;

        $biz['payment.platforms'] = $biz->factory(function ($biz) use ($paymentDefaultPlatforms) {
            $platforms = array();
            if (!empty($biz['payment.platforms.options'])) {
                foreach ($biz['payment.platforms.options'] as $key => $platform) {
                    if (!empty($paymentDefaultPlatforms[$key])) {
                        $platforms[$key] = array_merge($paymentDefaultPlatforms[$key], $biz['payment.platforms.options'][$key]);
                    }
                }
            }

            return $platforms;
        });

        foreach ($paymentDefaultPlatforms as $key => $platform) {
            $biz["payment.{$key}"] = function () use ($platform, $biz) {
                return new $platform['class']($biz);
            };
        }
    }

    private function registerStatus($biz)
    {
        $biz['payment_trade_context'] = function ($biz) {
            return new PayTradeContext($biz);
        };

        $statusArray = array(
            '\Codeages\Biz\Pay\Status\ClosedStatus',
            '\Codeages\Biz\Pay\Status\PayingStatus',
            '\Codeages\Biz\Pay\Status\ClosingStatus',
            '\Codeages\Biz\Pay\Status\PaidStatus',
            '\Codeages\Biz\Pay\Status\RefundingStatus',
            '\Codeages\Biz\Pay\Status\RefundedStatus',
        );

        foreach ($statusArray as $status) {
            $biz['payment_trade_status.'.$status::NAME] = function ($biz) use ($status) {
                return new $status($biz);
            };
        }
    }
}
