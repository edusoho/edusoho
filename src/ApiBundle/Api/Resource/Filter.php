<?php

namespace ApiBundle\Api\Resource;

use Codeages\Biz\Framework\Context\Biz;

abstract class Filter
{
    private $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    abstract function filter(&$data);

    public function filters(&$dataSet)
    {
        if (array_key_exists('data', $dataSet) && array_key_exists('paging', $dataSet)
        ) {
            foreach ($dataSet['data'] as &$data) {
                $this->filter($data);
            }
        }
    }
}