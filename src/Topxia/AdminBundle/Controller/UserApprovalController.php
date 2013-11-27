<?php

namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ImgConverToData;
use Topxia\AdminBundle\Form\UserApprovalApproveType;

class UserApprovalController extends BaseController
{

    public function approvingAction(Request $request)
    {
    	$paginator = new Paginator(
            $this->get('request'),
            $this->getUserService()->getUserCountByApprovalStatus('approving'),
            20
        );

    	$users = $this->getUserService()->getUsersByApprovalStatus(
            'approving',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        
        return $this->render('TopxiaAdminBundle:User:approving.html.twig', array(
        	'users' => $users,
        	'paginator' => $paginator
    	));
    }
    
    public function approvedAction(Request $request)
    {
        $paginator = new Paginator(
            $this->get('request'),
            $this->getUserService()->getUserCountByApprovalStatus('approved'),
            20
        );

        $users = $this->getUserService()->getUsersByApprovalStatus(
            'approved',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        
        return $this->render('TopxiaAdminBundle:User:approved.html.twig', array(
            'users' => $users,
            'paginator' => $paginator
        ));
    }

    public function approveAction(Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);

        $userApprovalInfo = $this->getUserService()->getLastestApprovalByUserIdAndStatus($user['id'], 'approving');

        if ($request->getMethod() == 'POST') {
            
            $data = $request->request->all();

            if($data['form_status'] == 'success'){
                $this->getUserService()->passApproval($id, $data['note']);
            } else if ($data['form_status'] == 'fail') {
                $this->getUserService()->rejectApproval($id, $data['note']);
            }

            return $this->createJsonResponse(array('status' => 'ok'));
        }

        return $this->render("TopxiaAdminBundle:User:user-approve-modal.html.twig",
            array(
                'user' => $user,
                'userApprovalInfo' => $userApprovalInfo
            )
        );
    }

    public function showIdcardAction($userId, $type)
    {
        $user = $this->getUserService()->getUser($userId);
        $currentUser = $this->getCurrentUser();

        if (empty($currentUser)) {
            throw $this->createAccessDeniedException();
        }

        $userApprovalInfo = $this->getUserService()->getLastestApprovalByUserIdAndStatus($user['id'], 'approving');

        $idcardPath = $type === 'back' ? $userApprovalInfo['backImg'] : $userApprovalInfo['faceImg'];
        $imgConverToData = new ImgConverToData;
        $imgConverToData -> getImgDir($idcardPath);
        $imgConverToData -> img2Data();
        $imgData = $imgConverToData -> data2Img();
        echo $imgData;
        exit;
    }


    public function cancelAction(Request $request, $id)
    {
        $this->getUserService()->rejectApproval($id, '管理员撤销');
        return $this->createJsonResponse(true);
    }

}
