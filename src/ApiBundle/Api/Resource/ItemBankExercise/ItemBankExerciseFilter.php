<?php

namespace ApiBundle\Api\Resource\ItemBankExercise;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Util\AssetHelper;
use ApiBundle\Api\Util\Money;

class ItemBankExerciseFilter extends Filter
{
    protected $simpleFields = [
        'id', 'title', 'cover',
    ];

    protected $publicFields = [
        'id', 'seq', 'title', 'status', 'chapterEnable', 'assessmentEnable', 'questionBankId',
        'categoryId', 'cover', 'studentNum', 'joinEnable', 'expiryMode', 'expiryDays', 'expiryStartDate', 'expiryEndDate',
        'isFree', 'price', 'originPrice', 'ratingNum', 'rating',
        'recommended', 'recommendedSeq', 'recommendedTime', 'createdTime', 'updatedTime', 'access',
    ];

    protected function publicFields(&$data)
    {
        $this->transformCover($data['cover']);
        $data['price2'] = Money::convert($data['price']);
        $data['originPrice2'] = Money::convert($data['originPrice']);
    }

    protected function simpleFields(&$data)
    {
        $this->transformCover($data['cover']);
    }

    private function transformCover(&$cover)
    {
        $cover['small'] = AssetHelper::getFurl(empty($cover['small']) ? '' : $cover['small'], 'item-bank-exercise.png');
        $cover['middle'] = AssetHelper::getFurl(empty($cover['middle']) ? '' : $cover['middle'], 'item-bank-exercise.png');
        $cover['large'] = AssetHelper::getFurl(empty($cover['large']) ? '' : $cover['large'], 'item-bank-exercise.png');
    }
}
