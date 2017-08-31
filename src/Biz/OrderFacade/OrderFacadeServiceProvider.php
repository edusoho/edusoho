<?php

namespace Biz\OrderFacade;

use Biz\OrderFacade\Command\Deduct\AvailableCouponCommand;
use Biz\OrderFacade\Command\Deduct\AvailableDeductWrapper;
use Biz\OrderFacade\Command\Deduct\PickCouponCommand;
use Biz\OrderFacade\Command\Deduct\PickedDeductWrapper;
use Biz\OrderFacade\Command\OrderPayCheck\CoinCheckCommand;
use Biz\OrderFacade\Command\OrderPayCheck\CouponCheckCommand;
use Biz\OrderFacade\Command\OrderPayCheck\OrderPayChecker;
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
        $biz['order.product.picked_deduct_wrapper'] = function ($biz) {
            $productPriceCalculator = new PickedDeductWrapper();
            $productPriceCalculator->setBiz($biz);

            $productPriceCalculator->addCommand(new PickCouponCommand());
            $productPriceCalculator->addCommand(new PickPaidCoursesCommand());

            return $productPriceCalculator;
        };

        $biz['order.product.available_deduct_wrapper'] = function ($biz) {
            $availableDeductWrapper = new AvailableDeductWrapper();
            $availableDeductWrapper->setBiz($biz);

            $availableDeductWrapper->addCommand(new AvailableCouponCommand());
            $availableDeductWrapper->addCommand(new AvailablePaidCoursesCommand());

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
                    'mch_id' => $paymentSetting['wxpay_account'],
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
