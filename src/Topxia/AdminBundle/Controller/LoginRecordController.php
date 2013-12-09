<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class LoginRecordController extends BaseController
{
	public function indexAction (Request $request)
    {
    	$conditions = $request->query->all();

        $conditions['action'] ='login_success';

        $paginator = new Paginator(
            $this->get('request'),
            $this->getLoginRecordService()->searchLoginRecordCount($conditions),
            20
        );

        $logRecords = $this->getLoginRecordService()->searchLoginRecord(
            $conditions,
            array('createdTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

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
            $this->getLoginRecordService()->findLoginRecordCountById($id),
            8
        );

        $loginRecords = $this->getLoginRecordService()->findLoginRecordById(
            $id,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('TopxiaAdminBundle:LoginRecord:login-record-details.html.twig',array(
            'user' => $user,
            'loginRecords' => $loginRecords,
            'loginRecordPaginator' => $paginator
        ));
    }

    private function getLoginRecordService()
    {
    	return $this->getServiceKernel()->createService('User.LoginRecordService');
    }
}