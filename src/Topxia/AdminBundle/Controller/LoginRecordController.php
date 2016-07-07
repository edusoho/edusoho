<?php
namespace Topxia\AdminBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\ConvertIpToolkit;
use Symfony\Component\HttpFoundation\Request;

class LoginRecordController extends BaseController
{
    public function indexAction(Request $request)
    {
        $user           = $this->getCurrentUser();
        $userConditions = $conditions     = $request->query->all();

        unset( $userConditions['startDateTime']);
        unset( $userConditions['endDateTime']);
        
        $userConditions = $this->fillOrgCode($userConditions);

        $users   = $this->getUserService()->searchUsers($userConditions, array('createdTime', 'DESC'), 0, 2000);
        $userIds = ArrayToolkit::column($users, 'id');

        if ($userIds) {
            $conditions['userIds'] = $userIds;
            unset($conditions['nickname']);
        } else {
            $paginator = new Paginator(
                $this->get('request'),
                0,
                20
            );
            return $this->render('TopxiaAdminBundle:LoginRecord:index.html.twig', array(
                'logRecords' => array(),
                'users'      => array(),
                'paginator'  => $paginator
            ));
        }

        $conditions['action'] = 'login_success';

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

        $userIds = ArrayToolkit::column($logRecords, 'userId');

        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('TopxiaAdminBundle:LoginRecord:index.html.twig', array(
            'logRecords' => $logRecords,
            'users'      => $users,
            'paginator'  => $paginator
        ));
    }

    public function showUserLoginRecordAction(Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);

        $conditions = array(
            'userId' => $user['id'],
            'action' => 'login_success',
            'module' => 'user'
        );

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

        return $this->render('TopxiaAdminBundle:LoginRecord:login-record-details.html.twig', array(
            'user'                 => $user,
            'loginRecords'         => $loginRecords,
            'loginRecordPaginator' => $paginator
        ));
    }

    protected function getLogService()
    {
        return $this->getServiceKernel()->createService('System.LogService');
    }
}
