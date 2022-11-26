<?php

namespace MarketingMallBundle\Api\Resource\QuestionBankCategory;

use ApiBundle\Api\Resource\Filter;

class QuestionBankCategoryFilter extends Filter
{
    protected $simpleFields = [
        'id', 'name', 'parentId', 'depth', 'children',
    ];

    public function simpleFields(&$data)
    {
        foreach ($data['children'] as &$child) {
            $this->filter($child);
        }
    }
}