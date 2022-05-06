<?php

namespace MarketingMallBundle\Api\Resource\Category;

use ApiBundle\Api\Resource\Filter;

class CategoryFilter extends Filter
{
//    protected $simpleFields = [
//        'id', 'name', 'parentId', 'depth', 'children',
//    ];

    public function simpleFields(&$data)
    {
//        foreach ($data['children'] as &$child) {
//            $this->filter($child);
//        }
    }
}