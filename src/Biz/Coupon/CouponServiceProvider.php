<?php


namespace Biz\Coupon;


use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class CouponServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritDoc
     */
    public function register(Container $biz)
    {
        $biz['coupon_factory'] = function($biz){
            return function ($couponTypeName) use ($biz) {
                if(!in_array($couponTypeName, array('vip', 'course'))){
                    throw new InvalidArgumentException('support vip or course, you give:'.$couponTypeName);
                }
                $cls = __NAMESPACE__ . '\\Type\\'.ucfirst($couponTypeName).'Coupon';
                return new $cls($biz);
            };
        };
    }

}