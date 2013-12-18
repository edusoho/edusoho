<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\ConvertIpToolkit;
use Topxia\Common\Paginator;

class LoginRecordController extends BaseController
{
	public function indexAction (Request $request)
    {
    	$conditions = $request->query->all();

        $conditions['action'] ='login_success';

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
            'users' => $users,
            'paginator' => $paginator
        ));
    }

    public function showUserLoginRecordAction (Request $request, $id)
    {
    	$user = $this->getUserService()->getUser($id);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getLogService()->searchLogCount(array('userId' => $user['id'])),
            8
        );

        $loginRecords = $this->getLogService()->searchLogs(
            array('userId' => $user['id']),
            'created',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $loginRecords = ConvertIpToolkit::ConvertIps($loginRecords);

        return $this->render('TopxiaAdminBundle:LoginRecord:login-record-details.html.twig',array(
            'user' => $user,
            'loginRecords' => $loginRecords,
            'loginRecordPaginator' => $paginator
        ));
    }

    protected function getLogService()
    {
    	return $this->getServiceKernel()->createService('System.LogService');
    }
}