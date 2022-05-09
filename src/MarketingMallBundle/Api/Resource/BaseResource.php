<?php

namespace MarketingMallBundle\Api\Resource;

use ApiBundle\Api\Resource\AbstractResource;

class BaseResource extends AbstractResource
{
    protected function preparePageCondition($conditions)
    {
        $limit = $conditions['size'] ?? static::DEFAULT_PAGING_LIMIT;
        $offset = static::DEFAULT_PAGING_OFFSET;
        if (!empty($conditions['page'])) {
            $offset = ($conditions['page'] - 1) * $limit;
        }

        return [(int)$offset, $limit];
    }

    protected function makePagingObject($objects, $total, $offset, $limit)
    {
        return [
            'data' => $objects,
            'page' => ($offset / $limit) + 1,
            'size' => $limit,
            'total' => $total,
        ];
    }
}