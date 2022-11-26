<?php

namespace MarketingMallBundle\Api\Resource\MallCategory;

use ApiBundle\Api\Resource\Filter;

class MallCategoryFilter extends Filter
{
    protected $simpleFields = [
        'id', 'name', 'parentId', 'depth', 'children',
    ];

    public function simpleFields(&$data)
    {
        $data['seq'] = $data['depth'];
        unset($data['depth']);
        foreach ($data['children'] as &$child) {
            $this->filter($child);
        }
    }
}