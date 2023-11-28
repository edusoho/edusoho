<?php

namespace ApiBundle\Api\Resource\QuestionBank;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\Item\ItemFilter;

class QuestionBankDuplicativeMaterialItemFilter extends Filter
{
    public function filter(&$data)
    {
        $itemFilter = new ItemFilter();
        $itemFilter->filters($data);
    }
}
