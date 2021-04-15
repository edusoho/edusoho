<?php

namespace AppBundle\Controller\AdminV2\Operating;

use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Symfony\Component\HttpFoundation\Request;

class UserContentAuditController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = $this->prepareConditions($request->query->all());



        $userAudits = [];
        $authors = [];
        $auditors = [];

        $paginator = new Paginator(
            $request,
           $this->getContentAuditService()->searchAuditCount($conditions),
            20
        );

        $userAudits = $this->getContentAuditService()->searchAudits($conditions,
            ['createdTime' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount());

        return $this->render('admin-v2/operating/user-content-audit/index.html.twig', [
            'userAudits' => $userAudits,
            'paginator' => $paginator,
            'authors' => $authors,
            'auditors' => $auditors,
        ]);
    }

    protected function prepareConditions($conditions)
    {
        if (!empty($conditions['author'])) {
            $user = $this->getUserService()->getUserByNickname($conditions['author']);
            unset($conditions['author']);
            $conditions['userIds'] = $user['id'] ? $user['id'] : -1;
        }

        if (!empty($conditions['startTime'])) {
            $conditions['startTime'] = strtotime($conditions['startTime']);
        }

        if (!empty($conditions['startTime'])) {
            $conditions['endTime'] = strtotime($conditions['endTime']);
        }

        return $conditions;
    }

    protected function getContentAuditService()
    {
        return $this->createService('AuditCenter:ContentAuditService');
    }
}
