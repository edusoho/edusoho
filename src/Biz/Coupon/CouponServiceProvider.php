<?php

namespace Biz\Coupon;

use AppBundle\Extension\Extension;
use Pimple\Container;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
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
                    throw new InvalidArgumentException('support vip or course, you give:'.$couponTypeName);
                }
                $cls = __NAMESPACE__.'\\Type\\'.ucfirst($couponTypeName).'Coupon';
                $instance = new $cls();
                $instance->setBiz($biz);

                return $instance;
            };
        };
    }
}
