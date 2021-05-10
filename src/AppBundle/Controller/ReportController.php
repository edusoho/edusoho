<?php

namespace AppBundle\Controller;

use AppBundle\Component\RateLimit\RateLimitException;
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

        $biz = $this->getBiz();
        $rateLimiter = $biz['ugc_report_rate_limiter'];
        $rateLimiter->handle($request);

        return $this->createJsonResponse(true);
    }

    public function tagsModalAction(Request $request, $targetType, $targetId)
    {
        try {
            $biz = $this->getBiz();
            $rateLimiter = $biz['ugc_report_rate_limiter'];
            $rateLimiter->getAllow();

            return $this->render('report/tags-modal.html.twig', [
                'contentTarget' => $request->query->get('contentTarget', ''),
                'modalTarget' => $request->query->get('modalTarget', ''),
                'targetType' => $targetType,
                'targetId' => $targetId,
            ]);
        } catch (RateLimitException $e) {
            return $this->render('report/rate-limit-modal.html.twig', [
                'message' => $e->getMessage(),
            ]);
        }
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
