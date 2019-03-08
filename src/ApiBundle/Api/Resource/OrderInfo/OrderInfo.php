<?php

namespace ApiBundle\Api\Resource\OrderInfo;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\MathToolkit;
use Biz\Common\CommonException;
use Biz\OrderFacade\Currency;
use Biz\OrderFacade\Exception\OrderPayCheckException;
use Biz\OrderFacade\Product\Product;
use Codeages\Biz\Pay\Service\AccountService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Biz\Coupon\Service\CouponService;

class OrderInfo extends AbstractResource
{
    public function add(ApiRequest $request)
    {
        $params = $request->request->all();

        if (empty($params['targetId']) || empty($params['targetType'])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        try {
            $this->addVipParams($params);
            $product = $this->getProduct($params['targetType'], $params);
            $product->validate();
            $product->setAvailableDeduct();
            $product->setPickedDeduct(array());

            return $this->getOrderInfoFromProduct($product);
        } catch (OrderPayCheckException $payCheckException) {
            throw new BadRequestHttpException($payCheckException->getMessage(), $payCheckException, $payCheckException->getCode());
        }
    }

    private function getOrderInfoFromProduct(Product $product)
    {
        $biz = $this->getBiz();
        /** @var $currency Currency */
        $currency = $biz['currency'];

        $user = $this->getCurrentUser();
        $balance = $this->getAccountService()->getUserBalanceByUserId($user->getId());

        $orderInfo = array(
            'targetId' => $product->targetId,
            'targetType' => $product->targetType,
            'cover' => $product->cover,
            'title' => $product->title,
            'maxRate' => $product->maxRate,
            'unitType' => $product->unit,
            'duration' => $product->num,
            'totalPrice' => $product->getPayablePrice(),
            'availableCoupons' => array(),
            'coinName' => '',
            'cashRate' => '1',
            'buyType' => '',
            'priceType' => 'CNY' == $currency->isoCode ? 'RMB' : 'Coin',
            'coinPayAmount' => 0,
            'fullCoinPayable' => 0,
            'verifiedMobile' => (isset($user['verifiedMobile'])) && (strlen($user['verifiedMobile']) > 0) ? $user['verifiedMobile'] : '',
            'hasPayPassword' => $this->getAccountService()->isPayPasswordSetted($user['id']),
            'account' => array(
                'id' => $balance['id'],
                'userId' => $balance['user_id'],
                'cash' => strval(MathToolkit::simple($balance['amount'], 0.01)),
            ),
        );

        if ($extra = $product->getCreateExtra()) {
            $orderInfo['buyType'] = $extra['buyType'];
        }

        if ($product->availableDeducts && isset($product->availableDeducts['coupon'])) {
            $orderInfo['availableCoupons'] = $product->availableDeducts['coupon'];

            foreach ($orderInfo['availableCoupons'] as &$availableCoupon) {
                $availableCoupon['target'] = $this->getCouponService()->getCouponTargetByTargetTypeAndTargetId($availableCoupon['targetType'], $availableCoupon['targetId']);
            }
        }

        $coinSetting = $this->service('System:SettingService')->get('coin');
        if (!empty($coinSetting['coin_name'])) {
            $orderInfo['coinName'] = $coinSetting['coin_name'];
        } else {
            $orderInfo['coinName'] = '虚拟币';
        }

        if (!empty($coinSetting['coin_enabled'])) {
            $orderInfo['cashRate'] = $coinSetting['cash_rate'];
            $orderInfo['coinPayAmount'] = round(round($orderInfo['totalPrice'], 2) * $orderInfo['cashRate'], 2);
            $orderInfo['maxCoin'] = round($orderInfo['coinPayAmount'] * $orderInfo['maxRate'] / 100, 2);
        }

        if ('Coin' == $orderInfo['priceType']) {
            $orderInfo['totalPrice'] = $currency->convertToCoin($orderInfo['totalPrice']);
        }

        return $orderInfo;
    }

    private function addVipParams(&$params)
    {
        if ('vip' == $params['targetType']) {
            $vipSetting = $this->service('System:SettingService')->get('vip');
            $defaultUnitType = 'month';
            $defaultDuration = 3;
            if ($vipSetting && !empty($vipSetting['buyType'])) {
                if (isset($params['unit'])) {
                    $this->checkUnit($vipSetting['buyType'], $params['unit']);
                }
                //按年月
                if ('10' == $vipSetting['buyType']) {
                    $defaultDuration = $vipSetting['default_buy_months10'];
                //按年
                } elseif ('20' == $vipSetting['buyType']) {
                    $defaultUnitType = 'year';
                    $defaultDuration = $vipSetting['default_buy_years'];
                //按月
                } else {
                    $defaultDuration = $vipSetting['default_buy_months'];
                }
            }

            $params['unit'] = !empty($params['unit']) ? $params['unit'] : $defaultUnitType;
            $params['num'] = !empty($params['num']) ? $params['num'] : $defaultDuration;
        }
    }

    private function checkUnit($type, $unit)
    {
        $result = true;
        switch ($type) {
            case '30':
                if ('month' !== $unit) {
                    $result = false;
                }
                break;
            case '20':
                if ('year' !== $unit) {
                    $result = false;
                }
                break;
            case '10':
            default:
                if (!in_array($unit, array('year', 'month'))) {
                    $result = false;
                }
                break;
        }

        if (!$result) {
            throw CommonException::ERROR_PARAMETER();
        }
    }

    private function getProduct($targetType, $params)
    {
        $biz = $this->getBiz();

        /* @var $product Product */
        $product = $biz['order.product.'.$targetType];

        $product->init($params);

        return $product;
    }

    /**
     * @return CouponService
     */
    private function getCouponService()
    {
        return $this->service('Coupon:CouponService');
    }

    /**
     * @return AccountService
     */
    private function getAccountService()
    {
        return $this->service('Pay:AccountService');
    }
}
