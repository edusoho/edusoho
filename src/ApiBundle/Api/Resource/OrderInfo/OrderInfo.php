<?php

namespace ApiBundle\Api\Resource\OrderInfo;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\Service\CourseService;
use Biz\OrderFacade\Currency;
use Biz\OrderFacade\Product\Product;
use Codeages\Biz\Framework\Pay\Service\AccountService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class OrderInfo extends AbstractResource
{
    public function add(ApiRequest $request)
    {
        $params = $request->request->all();

        if (empty($params['targetId']) || empty($params['targetType'])) {
            throw new BadRequestHttpException('缺少参数', null, ErrorCode::INVALID_ARGUMENT);
        }

        $this->addVipParams($params);

        $product = $this->getProduct($params['targetType'], $params);

        $product->setAvailableDeduct();
        $product->setPickedDeduct(array());

        return $this->getOrderInfoFromProduct($product);
    }

    private function getOrderInfoFromProduct(Product $product)
    {
        $orderInfo = array(
            'targetId' => $product->targetId,
            'targetType' => $product->targetType,
            'title' => $product->title,
            'maxRate' => $product->maxRate,
            'unitType' => $product->unit,
            'duration' => $product->num,
            'totalPrice' => $product->getPayablePrice(),
        );

        if ($extra = $product->getCreateExtra()) {
            $orderInfo['buyType'] = $extra['buyType'];
        }

        if ($product->availableDeducts && isset($product->availableDeducts['coupon'])) {
            $orderInfo['availableCoupons'] = $product->availableDeducts['coupon'];
        } else {
            $orderInfo['availableCoupons'] = array();
        }

        $user = $this->getCurrentUser();
        $balance = $this->getAccountService()->getUserBalanceByUserId($user->getId());
        $orderInfo['account'] = array(
            'id' => $balance['id'],
            'userId' => $balance['user_id'],
            'cash' => $balance['amount']
        );

        $orderInfo['verifiedMobile'] = (isset($user['verifiedMobile'])) && (strlen($user['verifiedMobile']) > 0) ? $user['verifiedMobile'] : '';
        $orderInfo['hasPayPassword'] = $this->getAccountService()->isPayPasswordSetted($user['id']);

        $coinSetting = $this->service('System:SettingService')->get('coin');
        if (!empty($coinSetting['coin_name'])) {
            $orderInfo['coinName'] = $coinSetting['coin_name'];
        } else {
            $orderInfo['coinName'] = '虚拟币';
        }

        $biz = $this->getBiz();
        /** @var  $currency  Currency */
        $currency = $biz['currency'];
        $orderInfo['priceType'] = $currency->isoCode == 'CNY' ? 'RMB' : 'Coin';
        if (!empty($coinSetting['coin_enabled'])) {
            $orderInfo['cashRate'] = $currency->exchangeRate;
            $orderInfo['coinPayAmount'] = round($orderInfo['totalPrice'] * $orderInfo['cashRate'], 2);
            $orderInfo['maxCoin'] = round($orderInfo['coinPayAmount'] * $orderInfo['maxRate'] / 100, 2);
        }

        return $orderInfo;
    }

    private function addVipParams(&$params)
    {
        if ($params['targetType'] == 'vip') {
            $vipSetting = $this->service('System:SettingService')->get('vip');
            $defaultUnitType = 'month';
            $defaultDuration = 3;
            if ($vipSetting && !empty($vipSetting['buyType'])) {
                //按年月
                if ($vipSetting['buyType'] == '10') {
                    $defaultDuration = $vipSetting['default_buy_months10'];
                    //按年
                } elseif ($vipSetting['buyType'] == '20') {
                    $defaultUnitType = 'year';
                    $defaultDuration = $vipSetting['default_buy_years'];
                    //按月
                } else {
                    $defaultDuration = $vipSetting['default_buy_months'];
                }
            }

            $params['unit'] = $defaultUnitType;
            $params['num'] = $defaultDuration;
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
     * @return AccountService
     */
    private function getAccountService()
    {
        return $this->service('Pay:AccountService');
    }
}