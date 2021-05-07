<?php

namespace AppBundle\Controller\AdminV2\Operating;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\AuditCenter\Service\ReportAuditService;
use Biz\AuditCenter\Service\ReportRecordService;
use Biz\AuditCenter\Service\ReportService;
use Symfony\Component\HttpFoundation\Request;

class ReportContentAuditController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = array_merge([
            'targetType' => '',
            'reportTag' => '',
            'status' => 'none_checked',
        ], $request->query->all());

        $paginator = new Paginator(
            $request,
            $this->getReportAuditService()->searchReportAuditCount($conditions),
            20
        );

        $reportAudits = $this->getReportAuditService()->searchReportAudits(
            $conditions,
            ['reportCount' => 'DESC', 'id' => 'ASC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        foreach ($reportAudits as &$reportAudit) {
            $context = $this->getReportService()->getReportSourceContext($reportAudit['targetType'], $reportAudit['targetId']);
            $reportAudit['content'] = empty($context['content']) ? $reportAudit['content'] : $context['content'];
            $reportAudit['contentUpdatedTime'] = empty($context['updatedTime']) ? $reportAudit['createdTime'] : $context['updatedTime'];
        }

        $userIds = array_merge(array_column($reportAudits, 'auditor'), array_column($reportAudits, 'author'));
        $users = empty($userIds) ? [] : ArrayToolkit::index($this->getUserService()->searchUsers(['userIds' => $userIds], [], 0, count($userIds)), 'id');

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
            $ids = is_array($ids) ? $ids : json_decode($ids, true);
            $this->getReportAuditService()->updateReportAuditStatusByIds($ids, $status);

            return $this->createJsonResponse(true);
        }

        return $this->render('admin-v2/operating/report-content-audit/confirm-modal.html.twig', [
            'params' => $request->query->all(),
            'status' => $status,
            'url' => $this->generateUrl('admin_v2_report_content_audit_batch_update', ['status' => $status]),
        ]);
    }

    public function reportRecordAction(Request $request, $auditId)
    {
        $conditions = ['auditId' => $auditId];
        $paginator = new Paginator(
            $request,
            $this->getReportRecordService()->searchReportRecordCount($conditions),
            20
        );

        $reportRecords = $this->getReportRecordService()->searchReportRecords($conditions, ['auditTime' => 'DESC'], $paginator->getOffsetCount(), $paginator->getPerPageCount());
        $userIds = array_column($reportRecords, 'reporter');
        $users = empty($userIds) ? [] : ArrayToolkit::index($this->getUserService()->searchUsers(['userIds' => $userIds], [], 0, count($userIds)), 'id');

        return $this->render('admin-v2/operating/report-content-audit/record-modal.html.twig', [
            'users' => $users,
            'reportRecords' => $reportRecords,
            'paginator' => $paginator,
        ]);
    }

    /**
     * @return ReportAuditService
     */
    protected function getReportAuditService()
    {
        return $this->createService('AuditCenter:ReportAuditService');
    }

    /**
     * @return ReportRecordService
     */
    protected function getReportRecordService()
    {
        return $this->createService('AuditCenter:ReportRecordService');
    }

    /**
     * @return ReportService
     */
    protected function getReportService()
    {
        return $this->createService('AuditCenter:ReportService');
    }
}
