<?php

namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\AdminBundle\Form\UserApprovalApproveType;

class UserApprovalController extends BaseController
{

    public function approvingAction(Request $request)
    {
    	$paginator = new Paginator(
            $this->get('request'),
            $this->getUserService()->getApprovingUserCount(),
            20
        );

    	$users = $this->getUserService()->getApprovingUsers(
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        
        return $this->render('TopxiaAdminBundle:User:approving.html.twig', array(
        	'users' => $users,
        	'paginator' => $paginator
    	));
    }

    public function approveAction(Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);
        $userApprovalInfo = $this->getUserService()->getApprovalByUserId($user['id']);

        if ($request->getMethod() == 'POST') {
            
            $data = $request->request->all();

            $this->getUserService()->passApproval($user['id'], $data['note']);

            return $this->createJsonResponse(array('status' => 'ok'));

        }

        return $this->render("TopxiaAdminBundle:User:user-approve-modal.html.twig",
            array(
                'user' => $user,
                'userApprovalInfo' => $userApprovalInfo
            )
        );
    }

    public function cancelAction(Request $request, $id)
    {
        $this->getUserService()->rejectApproval($id, '管理员撤销');
        return $this->createJsonResponse(true);
    }

}
