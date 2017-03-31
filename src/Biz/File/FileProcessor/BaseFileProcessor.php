<?php

namespace Biz\File\FileProcessor;

use Codeages\Biz\Framework\Context\Biz;

abstract class BaseFileProcessor
{
    /**
     * @var Biz
     */
    private $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    protected function getBiz()
    {
        return $this->biz;
    }
}
