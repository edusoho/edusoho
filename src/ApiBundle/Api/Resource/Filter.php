<?php

namespace ApiBundle\Api\Resource;

abstract class Filter
{
    abstract function filter(&$data);

    public function filters(&$dataSet)
    {
        if (array_key_exists('data', $dataSet) && array_key_exists('paging', $dataSet)
        ) {
            foreach ($dataSet['data'] as &$data) {
                $this->filter($data);
            }
        } else {
            foreach($dataSet as &$data) {
                $this->filter($data);
            }
        }
    }
}