<?php

namespace Biz\AuditCenter\Service;

interface ReportService
{
    public function submit($targetType, $targetId, $data);

    public function getReportSourceContext($targetType, $targetId);
}
