<?php

namespace Biz\Export;

use Codeages\Biz\Framework\Context\Biz;

abstract class Exporter implements ExporterInterface
{
    protected $biz;

    protected $request;

    final public function __construct(Biz $biz, $request)
    {
        $this->biz = $biz;
        $this->request = $request;
    }

    abstract public function getValue();

    abstract public function getTitles();

    abstract public function getExportContent();

    public function getPreResult()
    {

    }
}