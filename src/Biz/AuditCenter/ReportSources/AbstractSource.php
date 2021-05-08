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

    abstract public function handleSource($audit);

    protected function getAuditFields($audit)
    {
        $fields = [];

        if ('pass' === $audit['status']) {
            $fields['auditStatus'] = 'pass';
        } elseif ('illegal' === $audit['status']) {
            $fields['auditStatus'] = 'illegal';
        } elseif ('none_checked' === $audit['status']) {
            $fields['auditStatus'] = 'none_checked';
        }

        return $fields;
    }
}
