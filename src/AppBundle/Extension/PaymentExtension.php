<?php

namespace AppBundle\Extension;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class PaymentExtension extends Extension implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $container)
    {
    }

    public function getPayments()
    {
        return array(
            'wechat' => array(
                'notify' => 'AppBundle:Cashier/Wechat:notify',
                'return' => 'AppBundle:Cashier/Wechat:return',
                'returnForH5' => 'AppBundle:Cashier/Wechat:returnForH5',
            ),

            'alipay' => array(
                'notify' => 'AppBundle:Cashier/Alipay:notify',
                'return' => 'AppBundle:Cashier/Alipay:return',
                'returnForApp' => 'AppBundle:Cashier/Alipay:returnForApp',
                'returnForH5' => 'AppBundle:Cashier/Alipay:returnForH5',
            ),

            'lianlianpay' => array(
                'notify' => 'AppBundle:Cashier/Lianlianpay:notify',
                'return' => 'AppBundle:Cashier/Lianlianpay:return',
            ),

            'wechat_app' => array(
                'notify' => 'AppBundle:Cashier/Wechat:notify',
                'return' => 'AppBundle:Cashier/Wechat:return',
            ),
        );
    }
}
