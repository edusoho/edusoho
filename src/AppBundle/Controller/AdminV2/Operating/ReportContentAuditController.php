<?php

namespace AppBundle\Controller\AdminV2\Operating;

use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\AuditCenter\Service\ReportAuditService;
use Symfony\Component\HttpFoundation\Request;

class ReportContentAuditController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = array_merge([
            'targetType' => '',
            'reportTag' => '',
            'status' => 'none',
        ], $request->query->all());

        $paginator = new Paginator(
            $this->get('request'),
            $this->getReportAuditService()->searchReportAuditCount($conditions),
            20
        );

        $reportAudits = $this->getReportAuditService()->searchReportAudits(
            $conditions,
            ['reportCount' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userIds = array_merge(array_column($reportAudits, 'auditor'), array_column($reportAudits, 'author'));
        $users = empty($userIds) ? [] : $this->getUserService()->searchUsers(['ids' => $userIds], [], 0, count($userIds), ['id', 'nickname']);

        return $this->render('admin-v2/operating/report-content-audit/index.html.twig', [
            'reportAudits' => $reportAudits,
            'users' => array_column($users, null, 'id'),
            'paginator' => $paginator,
        ]);
    }

    public function updateStatusAction(Request $request, $id, $status)
    {
        if ($request->isMethod('POST')) {
            $this->getReportAuditService()->updateReportAuditStatus($id, $status);

            return $this->createJsonResponse(true);
        }

        return $this->render('admin-v2/operating/report-content-audit/confirm-modal.html.twig', [
            'status' => $status,
            'url' => $this->generateUrl('admin_v2_report_content_audit_update_status', ['id' => $id, 'status' => $status]),
        ]);
    }

    public function batchUpdateStatusAction(Request $request, $status)
    {
        if ($request->isMethod('POST')) {
            $ids = $request->request->get('ids');
            $ids = is_array($ids) ? $ids : json_decode($ids);
            $status = $request->request->get('status');
            $this->getReportAuditService()->updateReportAuditStatusByIds($ids, $status);

            return $this->createJsonResponse(true);
        }

        return $this->render('admin-v2/operating/report-content-audit/confirm-modal.html.twig', [
            'params' => $request->query->all(),
            'status' => $status,
            'url' => $this->generateUrl('admin_v2_report_content_audit_batch_update', ['status' => $status]),
        ]);
    }

    /**
     * @return ReportAuditService
     */
    protected function getReportAuditService()
    {
        return $this->createService('AuditCenter:ReportAuditService');
    }
}
