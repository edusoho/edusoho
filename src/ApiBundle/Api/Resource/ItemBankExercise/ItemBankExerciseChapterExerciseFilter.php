<?php

namespace ApiBundle\Api\Resource\ItemBankExercise;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\ItemBank\ItemCategoryFilter;

class ItemBankExerciseChapterExerciseFilter extends Filter
{
    protected $publicFields = [
        'module',
        'categories',
    ];

    protected function publicFields(&$data)
    {
        $itemBankExerciseModuleFilter = new ItemBankExerciseModuleFilter();
        $itemBankExerciseModuleFilter->filter($data['module']);

        $itemCategoryFilter = new ItemCategoryFilter();
        $itemCategoryFilter->filters($data['categories']);
    }
}
