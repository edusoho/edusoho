<?php

namespace Biz\OrderFacade;

use Biz\OrderFacade\Command\Deduct\AvailableCouponCommand;
use Biz\OrderFacade\Command\Deduct\AvailableDeductWrapper;
use Biz\OrderFacade\Command\Deduct\PickCouponCommand;
use Biz\OrderFacade\Command\Deduct\PickedDeductWrapper;
use Biz\OrderFacade\Command\OrderPayCheck\CoinCheckCommand;
use Biz\OrderFacade\Command\OrderPayCheck\CouponCheckCommand;
use Biz\OrderFacade\Command\OrderPayCheck\OrderPayChecker;
use Biz\OrderFacade\Deduct\CouponDeduct;
use Biz\OrderFacade\Product\ClassroomProduct;
use Biz\OrderFacade\Product\CourseProduct;
use Biz\System\Service\SettingService;
use Codeages\Biz\Framework\Context\Biz;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Biz\OrderFacade\Command\Deduct\AvailablePaidCoursesCommand;
use Biz\OrderFacade\Command\Deduct\PickPaidCoursesCommand;

class OrderFacadeServiceProvider implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        $this->registerProduct($biz);

        $this->registerDeduct($biz);

        $this->registerCommands($biz);

        $this->registerCurrency($biz);

        $this->registerPayments($biz);

        $this->registerIapOptions($biz);
    }

    private function registerProduct(Container $biz)
    {
        $biz[sprintf('order.product.%s', CourseProduct::TYPE)] = $biz->factory(function ($biz) {
            $product = new CourseProduct();
            $product->setBiz($biz);

            return $product;
        });

        $biz[sprintf('order.product.%s', ClassroomProduct::TYPE)] = $biz->factory(function ($biz) {
            $product = new ClassroomProduct();
            $product->setBiz($biz);

            return $product;
        });
    }

    private function registerDeduct(Container $biz)
    {
        $biz[sprintf('order.deduct.%s', CouponDeduct::TYPE)] = $biz->factory(function ($biz) {
            $deduct = new CouponDeduct();
            $deduct->setBiz($biz);

            return $deduct;
        });
    }

    private function registerCommands(Container $biz)
    {
        $biz['order.product.picked_deduct_wrapper'] = function ($biz) {
            $productPriceCalculator = new PickedDeductWrapper();
            $productPriceCalculator->setBiz($biz);

            $productPriceCalculator->addCommand(new PickCouponCommand(), 10);
            $productPriceCalculator->addCommand(new PickPaidCoursesCommand(), 20);

            return $productPriceCalculator;
        };

        $biz['order.product.available_deduct_wrapper'] = function ($biz) {
            $availableDeductWrapper = new AvailableDeductWrapper();
            $availableDeductWrapper->setBiz($biz);

            $availableDeductWrapper->addCommand(new AvailableCouponCommand(), 10);
            $availableDeductWrapper->addCommand(new AvailablePaidCoursesCommand(), 20);

            return $availableDeductWrapper;
        };

        $biz['order.pay.checker'] = function ($biz) {
            $payChecker = new OrderPayChecker();
            $payChecker->setBiz($biz);

            $payChecker->addCommand(new CouponCheckCommand());
            $payChecker->addCommand(new CoinCheckCommand());

            return $payChecker;
        };
    }

    private function registerPayments(Container $biz)
    {
        $biz['payment.platforms.options'] = function ($biz) {
            /** @var $settingService SettingService */
            /** @var $biz Biz */
            $settingService = $biz->service('System:SettingService');
            $paymentSetting = $settingService->get('payment', array());

            $enabledPayments = array();

            if (isset($paymentSetting['alipay_enabled']) && $paymentSetting['alipay_enabled']) {
                $enabledPayments['alipay'] = array(
                    'seller_email' => $paymentSetting['alipay_account'],
                    'partner' => $paymentSetting['alipay_key'],
                    'key' => $paymentSetting['alipay_secret'],
                );
            }

            if (isset($paymentSetting['wxpay_enabled']) && $paymentSetting['wxpay_enabled']) {
                $enabledPayments['wechat'] = array(
                    'appid' => $paymentSetting['wxpay_appid'],
                    'mch_id' => $paymentSetting['wxpay_account'],
                    'key' => $paymentSetting['wxpay_key'],
                    'secret' => $paymentSetting['wxpay_secret'],
                    'cert_path' => '',
                    'key_path' => '',
                );
            }

            if (isset($paymentSetting['llpay_enabled']) && $paymentSetting['llpay_enabled']) {
                $enabledPayments['lianlianpay'] = array(
                    'oid_partner' => $paymentSetting['llpay_key'],
                    'accessKey' => $paymentSetting['llpay_accessKey'],
                    'secret' => $paymentSetting['llpay_secretKey'],
                );
            }

            $wechatAppSetting = $settingService->get('wechat_app', array());
            if (isset($wechatAppSetting['enabled']) && $wechatAppSetting['enabled']) {
                $enabledPayments['wechat_app'] = array(
                    'appid' => $wechatAppSetting['appid'],
                    'mch_id' => $wechatAppSetting['account'],
                    'key' => $wechatAppSetting['key'],
                    'secret' => $wechatAppSetting['secret'],
                    'cert_path' => '',
                    'key_path' => '',
                );
            }

            return $enabledPayments;
        };

        $biz['payment.options'] = function ($biz) {
            $setting = $biz->service('System:SettingService')->get('coin');

            $site = $biz->service('System:SettingService')->get('site', array());

            return array(
                'closed_by_notify' => true,
                'coin_rate' => empty($setting['coin_enabled']) ? 1 : $setting['cash_rate'],
                'goods_title' => empty($site['name']) ? 'EduSoho订单' : $site['name'].'订单',
            );
        };

        $biz['order.options'] = array(
            'closed_expired_time' => 2 * 24 * 60 * 60,
        );
    }

    private function registerCurrency(Container $biz)
    {
        $biz['currency'] = function ($biz) {
            $currency = new Currency($biz);

            return $currency;
        };
    }

    private function registerIapOptions(Container $biz)
    {
        $biz['iap.options'] = function ($biz) {
            $mobileSetting = $biz->service('System:SettingService')->get('mobile', array());

            return array(
                'bundleId' => $mobileSetting['bundleId'],
                'product' => $biz->service('System:SettingService')->get('mobile_iap_product', array()),
            );
        };
    }
}
