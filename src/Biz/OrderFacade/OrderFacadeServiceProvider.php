<?php

namespace Biz\OrderFacade;

use Biz\OrderFacade\Command\CouponPriceCommand;
use Biz\OrderFacade\Command\ProductAvailableCouponCommand;
use Biz\OrderFacade\Command\ProductMarketingWrapper;
use Biz\OrderFacade\Command\ProductPriceCalculator;
use Biz\OrderFacade\Product\ClassroomProduct;
use Biz\OrderFacade\Product\CourseProduct;
use Biz\System\Service\SettingService;
use Codeages\Biz\Framework\Context\Biz;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class OrderFacadeServiceProvider implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        $this->registerProduct($biz);

        $this->registerCommands($biz);

        $this->registerCurrency($biz);

        $this->registerPayments($biz);
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

    private function registerCommands(Container $biz)
    {
        $biz['order.product.marketing_wrapper'] = function ($biz) {
            $productMarketingWrapper = new ProductMarketingWrapper();
            $productMarketingWrapper->setBiz($biz);

            $productMarketingWrapper->addCommand(new ProductAvailableCouponCommand());

            return $productMarketingWrapper;
        };

        $biz['order.product.price_calculator'] = function ($biz) {
            $productPriceCalculator = new ProductPriceCalculator();
            $productPriceCalculator->setBiz($biz);

            $productPriceCalculator->addCommand(new CouponPriceCommand());

            return $productPriceCalculator;
        };
    }

    private function registerPayments(Container $biz)
    {
        $biz['payment.platforms'] = function ($biz) {
            /** @var $settingService SettingService */
            /** @var $biz Biz */
            $settingService = $biz->service('System:SettingService');
            $paymentSetting = $settingService->get('payment', array());

            $enabledPayments = array();
            if ($paymentSetting['alipay_enabled']) {
                $enabledPayments['alipay.in_time'] = array(
                    'seller_email' => $paymentSetting['alipay_account'],
                    'partner' => $paymentSetting['alipay_secret'],
                    'key' => $paymentSetting['alipay_key'],
                );
            }

            if ($paymentSetting['wxpay_enabled']) {
                $enabledPayments['wechat'] = array(
                    'appid' => $paymentSetting['wxpay_appid'],
                    'mch_id' => $paymentSetting['wxpay_secret'],
                    'key' => $paymentSetting['wxpay_key'],
                    'cert_path' => '',
                    'key_path' => '',
                );
            }

            return $enabledPayments;
        };
    }

    private function registerCurrency(Container $biz)
    {
        $biz['currency'] = function ($biz) {
            $currency = new Currency($biz);

            return $currency;
        };
    }
}
