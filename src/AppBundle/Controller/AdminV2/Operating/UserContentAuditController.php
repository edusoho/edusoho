<?php

namespace AppBundle\Controller\AdminV2\Operating;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\AuditCenter\Service\ContentAuditService;
use Biz\Common\CommonException;
use Symfony\Component\HttpFoundation\Request;

class UserContentAuditController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = $this->prepareConditions($request->query->all());

        $paginatorTotal = empty($conditions) ? 0 : $this->getContentAuditService()->searchAuditCount($conditions);

        $paginator = new Paginator(
            $request,
            $paginatorTotal,
            20
        );

        $userAudits = $this->getContentAuditService()->searchAudits($conditions,
            ['createdTime' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount());

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
        $auditor = $this->getCurrentUser()->getId();

        $this->getContentAuditService()->updateAudit($id, [
            'auditTime' => time(),
            'status' => $status,
            'auditor' => $auditor,
        ]);

        return $this->createJsonResponse(true);
    }

    public function batchConfirmAction(Request $request)
    {
        $ids = $request->request->get('ids');
        $status = $request->request->get('status');

        $auditor = $this->getCurrentUser()->getId();

        foreach ($ids as $id) {
            $this->getContentAuditService()->updateAudit($id, [
                'auditTime' => time(),
                'status' => $status,
                'auditor' => $auditor,
            ]);
        }

        return $this->createJsonResponse(true);
    }

    protected function auditContentSensitiveWordsMarker($userAudits)
    {
        if (empty($userAudits)) {
            return [];
        }

        foreach ($userAudits as &$audit) {
            $audit['short_content'] = mb_substr($audit['content'], 0, 200);
            $sensitiveWords = $audit['sensitiveWords'];
            if (empty($sensitiveWords)) {
                continue;
            }
            array_walk($sensitiveWords, function ($word) use (&$audit) {
                $audit['content'] = str_replace($word, "<span style='color: red'>{$word}</span>", $audit['content']);
                $audit['short_content'] = str_replace($word, "<span style='color: red'>{$word}</span>", $audit['short_content']);
            });
        }

        return  $userAudits;
    }

    protected function prepareConditions($conditions)
    {
        $conditions['minId'] = 0;

        $statusAll = ['sysPass', 'none', 'pass', 'illegal'];

        if (!empty($conditions['status']) && !in_array($conditions['status'], $statusAll)) {
            unset($conditions['status']);
        }

        if (!empty($conditions['author'])) {
            $user = $this->getUserService()->getUserByNickname($conditions['author']);
            $conditions['author'] = $user['id'] ? $user['id'] : -1;
        } else {
            unset($conditions['author']);
        }

        if (!empty($conditions['startTime'])) {
            $conditions['startTime'] = strtotime($conditions['startTime']);
        } else {
            unset($conditions['startTime']);
        }

        if (!empty($conditions['startTime'])) {
            $conditions['endTime'] = strtotime($conditions['endTime']);
        } else {
            unset($conditions['endTime']);
        }

        if (isset($conditions['containSensitiveWords'])) {
            switch ($conditions['containSensitiveWords']) {
                case 1:
                    $conditions['notContainSensitiveWords'] = '';
                    break;
                case 2:
                    $conditions['sensitiveWords'] = '';
                    break;
                default:
                    $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
                    break;
            }
            unset($conditions['containSensitiveWords']);
        }

        if (empty($conditions['targetType'])) {
            unset($conditions['targetType']);
        }
        if (empty($conditions['sensitiveWordsSearch'])) {
            unset($conditions['sensitiveWordsSearch']);
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
