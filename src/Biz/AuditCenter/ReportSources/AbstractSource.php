<?php

namespace Biz\AuditCenter\ReportSources;

use Codeages\Biz\Framework\Context\Biz;

abstract class AbstractSource
{
    /**
     * @var Biz
     */
    protected $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    /**
     * @return mixed
     */
    abstract public function getReportContext($targetId);
}
