<?php

namespace AppBundle\Controller\AdminV2\Operating;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\AuditCenter\Service\ContentAuditService;
use Symfony\Component\HttpFoundation\Request;

class UserContentAuditController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = $this->prepareConditions($request->query->all());

        $paginator = new Paginator(
            $request,
            $this->getContentAuditService()->searchAuditCount($conditions),
            20
        );

        $userAudits = $this->getContentAuditService()->searchAudits($conditions,
            ['updatedTime' => 'ASC', 'id' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userAudits = $this->auditContentSensitiveWordsMarker($userAudits);

        $userIds = array_merge(ArrayToolkit::column($userAudits, 'author'), ArrayToolkit::column($userAudits, 'auditor'));
        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('admin-v2/operating/user-content-audit/index.html.twig', [
            'userAudits' => $userAudits,
            'paginator' => $paginator,
            'users' => $users,
        ]);
    }

    public function confirmAction(Request $request, $id, $status)
    {
        if ($request->isMethod('POST')) {
            $auditor = $this->getCurrentUser()->getId();
            $this->getContentAuditService()->confirmUserAudit($id, $status, $auditor);

            return $this->createJsonResponse(true);
        }

        return $this->render('admin-v2/operating/user-content-audit/audit-modal.html.twig', [
            'status' => $status,
            'url' => $this->generateUrl('admin_v2_user_content_audit_confirm', ['id' => $id, 'status' => $status]),
        ]);
    }

    public function batchConfirmAction(Request $request, $status)
    {
        if ($request->isMethod('POST')) {
            $ids = $request->request->get('ids');
            $ids = is_array($ids) ? $ids : json_decode($ids, true);
            $auditor = $this->getCurrentUser()->getId();
            $this->getContentAuditService()->batchConfirmUserAuditByIds($ids, $status, $auditor);

            return $this->createJsonResponse(true);
        }

        return $this->render('admin-v2/operating/user-content-audit/audit-modal.html.twig', [
            'params' => $request->query->all(),
            'status' => $status,
            'url' => $this->generateUrl('admin_v2_user_content_audit_batch_confirm', ['status' => $status]),
        ]);
    }

    protected function auditContentSensitiveWordsMarker($userAudits)
    {
        if (empty($userAudits)) {
            return [];
        }

        foreach ($userAudits as &$audit) {
            $audit['short_content'] = $audit['content'];
            if (strlen($audit['content']) > 200) {
                $audit['short_content'] = mb_substr($audit['content'], 0, 200).'...';
            }
            $sensitiveWords = $audit['sensitiveWords'];
            if (empty($sensitiveWords)) {
                continue;
            }
            array_walk($sensitiveWords, function ($word) use (&$audit) {
                $audit['content'] = str_replace($word, "<span class='text-danger'>{$word}</span>", $audit['content']);
                $audit['short_content'] = str_replace($word, "<span class='text-danger'>{$word}</span>", $audit['short_content']);
            });
        }

        return  $userAudits;
    }

    protected function prepareConditions($conditions)
    {
        if (empty($conditions['status'])) {
            $conditions['status'] = 'none_checked';
        }

        if ('sys_checked' === $conditions['status']) {
            $conditions['auditor'] = -1;
        }

        if (!empty($conditions['author'])) {
            $user = $this->getUserService()->getUserByNickname($conditions['author']);
            $conditions['author'] = $user['id'] ? $user['id'] : -1;
        }

        if (!empty($conditions['status']) && !in_array($conditions['status'], ['none_checked', 'pass', 'illegal'])) {
            unset($conditions['status']);
        }

        if (!empty($conditions['startTime'])) {
            $conditions['startTime'] = strtotime($conditions['startTime']);
        }

        if (!empty($conditions['endTime'])) {
            $conditions['endTime'] = strtotime($conditions['endTime']);
        }

        if (isset($conditions['sensitiveWordsFilter'])) {
            switch ($conditions['sensitiveWordsFilter']) {
                case 1:
                    $conditions['containSensitiveWords'] = 0;
                    break;
                case 2:
                    $conditions['notContainSensitiveWords'] = 0;
                    break;
                default:
                    break;
            }
            unset($conditions['sensitiveWordsFilter']);
        }

        return $conditions;
    }

    /**
     * @return ContentAuditService
     */
    protected function getContentAuditService()
    {
        return $this->createService('AuditCenter:ContentAuditService');
    }
}
