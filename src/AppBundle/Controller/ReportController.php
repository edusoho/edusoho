<?php

namespace AppBundle\Controller;

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
        $data = [
            'reporter' => $this->getCurrentUser()->getId(),
            'reportTags' => [$request->request->get('reportTag')],
        ];
        $this->getReportService()->submit($targetType, $targetId, $data);

        return $this->createJsonResponse(true);
    }

    public function tagsModalAction(Request $request, $targetType, $targetId)
    {
        return $this->render('report/tags-modal.html.twig', [
            'targetType' => $targetType,
            'targetId' => $targetId,
        ]);
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
