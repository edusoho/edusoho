<?php

namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\ImgConverToData;
use Topxia\AdminBundle\Form\UserApprovalApproveType;

class UserApprovalController extends BaseController
{

    public function approvingAction(Request $request)
    {
        $fields = $request->query->all();

        $conditions = array(
            'roles'=>'',
            'keywordType'=>'',
            'keyword'=>'',
            'approvalStatus' => 'approving'
        );

        if(empty($fields)){
            $fields = array();
        }

        $conditions = array_merge($conditions, $fields);

        if(isset($fields['keywordType']) && ($fields['keywordType'] == 'truename' || $fields['keywordType'] == 'idcard')){
            //根据条件从user_approval表里查找数据
            $approvalcount = $this->getUserService()->searchapprovalsCount($conditions);
            $profiles = $this->getUserService()->searchapprovals($conditions,array('id','DESC'),0,$approvalcount);
            $userApprovingId = ArrayToolkit::column($profiles, 'userId');
        }else{
            $usercount = $this->getUserService()->searchUserCount($conditions);
            $profiles = $this->getUserService()->searchUsers($conditions,array('id','DESC'),0,$usercount);
            $userApprovingId = ArrayToolkit::column($profiles,'id');
        }

        //在user表里筛选正在被实名认证的
        $userConditions = array(
            'userIds' => $userApprovingId,
            'approvalStatus' => 'approving',
            );
        if (!empty($conditions['startDateTime']) && !empty($conditions['endDateTime'])) {
            $userConditions['startApprovedTime'] = strtotime($conditions['startDateTime']);
            $userConditions['endApprovedTime'] = strtotime($conditions['endDateTime']);
        }

        $userApprovalcount = 0;
        if(!empty($userApprovingId)){
            $userApprovalcount = $this->getUserService()->searchUserCount($userConditions);    
        }
        $paginator = new Paginator(
            $this->get('request'),
            $userApprovalcount,
            20
        );
        $users = array();
        if(!empty($userApprovingId)){
            $users = $this->getUserService()->searchUsers(
                $userConditions,
                array('id','DESC'),
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
            );
        } 

        //最终结果
        $approvingUserids = ArrayToolkit::column($users, 'id');
        $userProfiles = $this->getUserService()->searchapprovals(
            $approvingUserids,
            array('id','DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $approvals = $this->getUserService()->findUserApprovalsByUserIds(ArrayToolkit::column($users, 'id'));
        $approvals = ArrayToolkit::index($approvals, 'userId');

        return $this->render('TopxiaAdminBundle:User:approving.html.twig', array(
        	'users' => $users,
        	'paginator' => $paginator,
            'approvals' => $approvals
    	));
    }
    
    public function approvedAction(Request $request)
    {
        $fields = $request->query->all();

        $conditions = array(
            'roles'=>'',
            'keywordType'=>'',
            'keyword'=>'',
            'approvalStatus' => 'approved'
        );

        if(empty($fields)){
            $fields = array();
        }

        $conditions = array_merge($conditions, $fields);

        if(isset($fields['keywordType']) && ($fields['keywordType'] == 'truename' || $fields['keywordType'] == 'idcard')){
            //根据条件从user_approval表里查找数据
            $profilecount = $this->getUserService()->searchapprovalsCount($conditions);
            $profiles = $this->getUserService()->searchapprovals($conditions,array('id','DESC'),0,$profilecount);
            $userProfilesid = ArrayToolkit::column($profiles, 'userId');
        }else{
            $usercount = $this->getUserService()->searchUserCount($conditions);
            $profiles = $this->getUserService()->searchUsers($conditions,array('id','DESC'),0,$usercount);
            $userProfilesid = ArrayToolkit::column($profiles, 'id');
        }

        //在user表里筛选通过实名认证的
        $userConditions = array(
            'userIds' => $userProfilesid,
            'approvalStatus' => 'approved',
            );
        if (!empty($conditions['startDateTime']) && !empty($conditions['endDateTime'])) {
            $userConditions['startApprovedTime'] = strtotime($conditions['startDateTime']);
            $userConditions['endApprovedTime'] = strtotime($conditions['endDateTime']);
        } 

        $userApprovalcount = 0;
        if(!empty($userProfilesid)){
            $userApprovalcount = $this->getUserService()->searchUserCount($userConditions);    
        }

        $paginator = new Paginator(
            $this->get('request'),
            $userApprovalcount,
            20
        );
        $users = array();
        if(!empty($userProfilesid)){
            $users = $this->getUserService()->searchUsers(
                $userConditions,
                array('id','DESC'),
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
            );
        } 

        //最终结果
        $approvedUserids = ArrayToolkit::column($users, 'id');
        $userProfiles = $this->getUserService()->searchUserProfiles(
            $approvedUserids,
            array('id','DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

            $userProfiles = $this->getUserService()->findUserProfilesByIds(ArrayToolkit::column($users, 'id'));
            $userProfiles = ArrayToolkit::index($userProfiles, 'id');
        return $this->render('TopxiaAdminBundle:User:approved.html.twig', array(
            'users' => $users,
            'paginator' => $paginator,
            'userProfiles' => $userProfiles
        ));
    }
    public function approveAction(Request $request, $id)
    {
        list($user, $userApprovalInfo) = $this->getApprovalInfo($request, $id);

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
                'userApprovalInfo' => $userApprovalInfo,
            )
        );
    }

    public function viewApprovalInfoAction(Request $request, $id){
        list($user, $userApprovalInfo) = $this->getApprovalInfo($request, $id);

        return $this->render("TopxiaAdminBundle:User:user-approve-info-modal.html.twig",
            array(
                'user' => $user,
                'userApprovalInfo' => $userApprovalInfo,
            )
        );
    }

    protected function getApprovalInfo(Request $request, $id){
        $user = $this->getUserService()->getUser($id);

        $userApprovalInfo = $this->getUserService()->getLastestApprovalByUserIdAndStatus($user['id'], 'approving');
        return array($user, $userApprovalInfo);
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
