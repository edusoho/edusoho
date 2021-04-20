<?php

namespace AppBundle\Controller;

use AppBundle\Common\ArrayToolkit;
use Biz\AuditCenter\Service\ReportAuditService;
use Biz\AuditCenter\Service\ReportRecordService;
use Biz\AuditCenter\Service\ReportService;
use Symfony\Component\HttpFoundation\Request;

class ReportController extends BaseController
{
    public function submitAction(Request $request)
    {
        $targetType = $request->request->get('targetType');
        $targetId = $request->request->get('targetId');
        $data = ArrayToolkit::parts($request->request->all(), ['reasons' => [$request->request->get('reason')]]);
        $this->getReportService()->submit($targetType, $targetId, $data);

        return $this->createJsonResponse(true);
    }

    /**
     * @return ReportRecordService
     */
    protected function getReportRecordService()
    {
        return $this->createService('AuditCenter:ReportRecordService');
    }

    /**
     * @return ReportAuditService
     */
    protected function getReportAuditService()
    {
        return $this->createService('AuditCenter:ReportAuditService');
    }

    /**
     * @return ReportService
     */
    protected function getReportService()
    {
        return $this->createService('AuditCenter:ReportService');
    }
}
