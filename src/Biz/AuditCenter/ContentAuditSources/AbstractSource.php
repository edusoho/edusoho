<?php

namespace Biz\AuditCenter\ContentAuditSources;

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

    abstract public function handleSource($audit);

    protected function getAuditFields($audit)
    {
        $fields = [];

        if ('pass' === $audit['status']) {
            $fields['auditStatus'] = 'pass';
            $fields['content'] = $audit['content'];
        } elseif ('illegal' === $audit['status']) {
            $fields['auditStatus'] = 'illegal';
        } elseif ('none_checked' === $audit['status']) {
            $fields['auditStatus'] = 'none_checked';
        }

        return $fields;
    }
}
