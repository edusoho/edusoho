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
            'wxpay' => array(
                'notifyController' => 'AppBundle:PayCenter/WxpayNotify',
            ),
        );
    }
}
