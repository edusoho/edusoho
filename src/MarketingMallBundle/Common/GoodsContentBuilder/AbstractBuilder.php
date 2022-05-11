<?php

namespace MarketingMallBundle\Common\GoodsContentBuilder;

use AppBundle\Common\Exception\AbstractException;
use Codeages\Biz\Framework\Context\Biz;

abstract class AbstractBuilder
{
    /**
     * @var Biz
     */
    protected $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    abstract public function build($id);

    protected function createNewException($e)
    {
        if ($e instanceof AbstractException) {
            throw $e;
        }

        throw new \Exception();
    }
}
