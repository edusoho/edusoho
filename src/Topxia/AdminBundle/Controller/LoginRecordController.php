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
        $userCondotions = array();
        $user = '' ;
        if (!empty($conditions['keywordType'])) {
            $userCondotions['keywordType'] =$conditions["keywordType"];
        }

        if (!empty($conditions['keyword'])) {
            $userCondotions['keyword'] =$conditions["keyword"];
        }

        if(isset($userCondotions['keywordType']) && isset($userCondotions['keyword'])){
            $user = $this->getUserService()->searchUsers($userCondotions,array('createdTime', 'DESC'),0,2000);
            $userIds = ArrayToolkit::column($user, 'id');
            if($userIds){
                $conditions['userIds'] = $userIds;
            }else{
                $conditions[$conditions["keywordType"]] = $conditions["keyword"];
            }
        }

        $conditions['action'] ='login_success';
        if(!empty($conditions['email'])){
            $user=$this->getUserService()->getUserByEmail($conditions['email']) ;
            $conditions['userId']=empty($user) ? -1 : $user['id'];
        }

        unset($conditions['nickname']);

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

        if (isset($conditions["keywordType"])) {
            if(empty($user) && $conditions["keywordType"] == 'nickname' && !empty($conditions['keyword'])){
                $logRecords = array();
            }
        }

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