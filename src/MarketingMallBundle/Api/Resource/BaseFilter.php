<?php

namespace MarketingMallBundle\Api\Resource;

use ApiBundle\Api\Resource\Filter;

class BaseFilter extends Filter
{
    public function filters(&$dataSet)
    {
        if (!$dataSet || !is_array($dataSet)) {
            return;
        }
        if (array_key_exists('data', $dataSet) && array_key_exists('page', $dataSet)) {
            foreach ($dataSet['data'] as &$data) {
                $this->filter($data);
            }
        } else {
            foreach ($dataSet as &$data) {
                $this->filter($data);
            }
        }
    }
}