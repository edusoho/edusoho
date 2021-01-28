<?php

namespace Biz\Coupon;

use AppBundle\Extension\Extension;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class CouponServiceProvider extends Extension implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $biz)
    {
        $biz['coupon_factory'] = function ($biz) {
            return function ($couponTypeName) use ($biz) {
                if (!in_array($couponTypeName, array('vip', 'course', 'classroom'))) {
                    throw CouponException::TYPE_INVALID();
                }
                $cls = __NAMESPACE__.'\\Type\\'.ucfirst($couponTypeName).'Coupon';
                $instance = new $cls();
                $instance->setBiz($biz);

                return $instance;
            };
        };
    }
}
