<?php

namespace Codeages\Biz\Framework\Provider;

use Codeages\Biz\Framework\Pay\Status\PaymentTradeContext;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class PayServiceProvider implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        $biz['migration.directories'][] = dirname(dirname(__DIR__)).'/migrations/pay';
        $biz['autoload.aliases']['Pay'] = 'Codeages\Biz\Framework\Pay';

        $biz['payment.options'] = function () {
            return array(
                'closed_notify' => false,
                'refunded_notify' => false
            );
        };

        $biz['console.commands'][] = function () use ($biz) {
            return new \Codeages\Biz\Framework\Pay\Command\TableCommand($biz);
        };

        $biz['console.commands'][] = function () use ($biz) {
            return new \Codeages\Biz\Framework\Pay\Command\CashflowAddTitleCommand($biz);
        };

        $this->registerStatus($biz);
        $this->registerPayments($biz);
    }

    protected function registerPayments($biz)
    {
        $paymentDefaultPlatforms = array(
            'wechat' => array(
                'class' => '\Codeages\Biz\Framework\Pay\Payment\WechatGetway',
                'appid' => '',
                'mch_id' => '',
                'key' => '',
                'cert_path' => '',
                'key_path' => '',
            ),
            'alipay.in_time' => array(
                'class' => '\Codeages\Biz\Framework\Pay\Payment\AlipayInTimeGetway',
                'seller_email' => '',
                'partner' => '',
                'key' => '',
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
            return new PaymentTradeContext($biz);
        };

        $statusArray = array(
            '\Codeages\Biz\Framework\Pay\Status\ClosedStatus',
            '\Codeages\Biz\Framework\Pay\Status\PayingStatus',
            '\Codeages\Biz\Framework\Pay\Status\ClosingStatus',
            '\Codeages\Biz\Framework\Pay\Status\PaidStatus',
            '\Codeages\Biz\Framework\Pay\Status\RefundingStatus',
            '\Codeages\Biz\Framework\Pay\Status\RefundedStatus',
        );

        foreach ($statusArray as $status) {
            $biz['payment_trade_status.'.$status::NAME] = function ($biz) use ($status) {
                return new $status($biz);
            };
        }
    }
}
