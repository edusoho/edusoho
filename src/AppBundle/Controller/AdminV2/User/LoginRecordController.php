<?php

namespace AppBundle\Controller\AdminV2\User;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\ConvertIpToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\System\Service\LogService;
use Symfony\Component\HttpFoundation\Request;

class LoginRecordController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = [
            'module' => 'user',
            'action' => 'login_success',
            'startDateTime' => $request->query->get('startDateTime'),
            'endDateTime' => $request->query->get('endDateTime'),
        ];

        $userConditions = [
            'keywordType' => $request->query->get('keywordType'),
            'keyword' => $request->query->get('keyword'),
            'orgCode' => $request->query->get('orgCode'),
        ];
        $userConditions = $this->fillOrgCode($userConditions);
        if (!empty($userConditions['keyword'])) {
            $users = $this->getUserService()->searchUsers($userConditions, ['createdTime' => 'DESC'], 0, PHP_INT_MAX, ['id']);
            $conditions['userIds'] = empty($users) ? [-1] : ArrayToolkit::column($users, 'id');
        }

        $paginator = new Paginator(
            $this->get('request'),
            $this->getLogService()->searchLogCount($conditions),
            20
        );

        $logRecords = $this->getLogService()->searchLogs(
            $conditions,
            'created',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $logRecords = ConvertIpToolkit::ConvertIps($logRecords);

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($logRecords, 'userId'));

        return $this->render('admin-v2/user/login-record/index.html.twig', [
            'logRecords' => $logRecords,
            'users' => $users,
            'paginator' => $paginator,
        ]);
    }

    public function showUserLoginRecordAction(Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);

        $conditions = [
            'userId' => $user['id'],
            'actions' => ['login_success', 'user_login'],
            'modules' => ['user', 'mobile'],
        ];

        $paginator = new Paginator(
            $this->get('request'),
            $this->getLogService()->searchLogCount($conditions),
            8
        );

        $loginRecords = $this->getLogService()->searchLogs(
            $conditions,
            'created',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $loginRecords = ConvertIpToolkit::ConvertIps($loginRecords);

        return $this->render('admin-v2/user/login-record/login-record-details.html.twig', [
            'user' => $user,
            'loginRecords' => $loginRecords,
            'loginRecordPaginator' => $paginator,
        ]);
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }
}
