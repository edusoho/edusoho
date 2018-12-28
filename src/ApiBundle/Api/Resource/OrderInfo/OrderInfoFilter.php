<?php

namespace ApiBundle\Api\Resource\OrderInfo;

use ApiBundle\Api\Resource\Coupon\CouponFilter;
use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Util\AssetHelper;

class OrderInfoFilter extends Filter
{
    public function filter(&$data)
    {
        $data['availableCoupons'] = array_values($data['availableCoupons']);
        $couponFilter = new CouponFilter();
        $couponFilter->filters($data['availableCoupons']);
        $this->updateFullPath($data);
        $orderInfo = array(
            'targetId' => $data['targetId'],
            'targetType' => $data['targetType'],
            'cover' => $data['cover'],
            'totalPrice' => strval($data['totalPrice']),
            'title' => $data['title'],
            'account' => empty($data['account']) ? new \stdClass() : $data['account'],
            'hasPayPassword' => empty($data['hasPayPassword']) ? 0 : 1,
            'verifiedMobile' => empty($data['verifiedMobile']) ? '' : $data['verifiedMobile'],
            'coinName' => $data['coinName'],
            'cashRate' => empty($data['cashRate']) ? 0 : $data['cashRate'],
            'priceType' => empty($data['priceType']) ? 'RMB' : $data['priceType'],
            'coinPayAmount' => empty($data['coinPayAmount']) ? 0 : strval($data['coinPayAmount']),
            'maxCoin' => empty($data['maxCoin']) ? 0 : strval($data['maxCoin']),
            'availableCoupons' => $data['availableCoupons'],
            'unitType' => isset($data['unitType']) ? $data['unitType'] : '',
            'duration' => isset($data['duration']) ? $data['duration'] : '',
            'buyType' => isset($data['buyType']) ? $data['buyType'] : '',
            'fullCoinPayable' => $this->fullCoinPayable($data),
        );

        $data = $orderInfo;
    }

    private function fullCoinPayable($data)
    {
        if (empty($data['priceType'])) {
            return 0;
        }

        if ('Coin' == $data['priceType']) {
            return 1;
        }

        if (!empty($data['coinPayAmount']) && 0 != $data['cashRate'] && 100 == $data['maxRate']) {
            return 1;
        }

        return 0;
    }

    protected function updateFullPath(&$data)
    {
        if ('vip' == $data['targetType']) {
            $data['cover']['small'] = empty($data['cover']['small']) ? $this->convertFilePath('/assets/v2/img/vip/vip_icon_bronze.png') : $this->convertFilePath($data['cover']['small']);
            $data['cover']['middle'] = empty($data['cover']['middle']) ? $this->convertFilePath('/assets/v2/img/vip/vip_icon_bronze.png') : $this->convertFilePath($data['cover']['middle']);
            $data['cover']['large'] = empty($data['cover']['large']) ? $this->convertFilePath('/assets/v2/img/vip/vip_icon_bronze.png') : $this->convertFilePath($data['cover']['large']);
        } else {
            $data['cover']['small'] = AssetHelper::getFurl(empty($data['cover']['small']) ? '' : $data['cover']['small'], $data['targetType'].'.png');
            $data['cover']['middle'] = AssetHelper::getFurl(empty($data['cover']['middle']) ? '' : $data['cover']['middle'], $data['targetType'].'.png');
            $data['cover']['large'] = AssetHelper::getFurl(empty($data['cover']['large']) ? '' : $data['cover']['large'], $data['targetType'].'.png');
        }
    }
}
