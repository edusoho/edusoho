<?php

namespace Codeages\Weblib\Routing;

use Codeages\Weblib\Error\ResourceNotFoundException;

trait ResourceTrait
{
    abstract function getResource($name);

    protected function makeResult($item, $filter = null)
    {
        if (empty($item)) {
            throw new ResourceNotFoundException();
        }

        if (!$filter) {
            return $item;
        }

        return $this->getResource($filter)->filter($item);
    }

    protected function makeResultSet(array $items, $filter = null)
    {
        if ($filter) {
            foreach ($items as $key => $item) {
                $items[$key] = $this->getResource($filter)->filter($item);
            }
        }

        return $items;
    }

    protected function makePagedResultSet($items, $total, $start, $limit, $filter = null)
    {
        if ($filter) {
            foreach ($items as $key => $item) {
                $items[$key] = $this->getResource($filter)->filter($item);
            }
        }

        return array(
            'data' => $items,
            'paging' => array(
                'total' => $total,
                'start' => $start,
                'limit' => $limit,
            ),
        );
    }

    protected function getOrderBy($condition)
    {
        if (empty($condition['sort'])) {
            return array('created_time' => 'DESC');
        }
        $orderBy = array();
        foreach (explode(',', $condition['sort']) as $str) {
            if (strpos($str, '-') === 0) {
                $orderBy[trim($str, '-')] = 'DESC';
            } else {
                $orderBy[trim($str, ' ')] = 'ASC';
            }
        }

        return $orderBy;
    }
}
