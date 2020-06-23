<?php

namespace ApiBundle\Api\Resource\ItemBankExercise;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Util\AssetHelper;

class ItemBankExerciseFilter extends Filter
{
    protected $simpleFields = [
        'id', 'title', 'cover',
    ];

    protected $publicFields = [];

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
