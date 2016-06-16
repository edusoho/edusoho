<?php

namespace Topxia\AdminBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\ImgConverToData;
use Symfony\Component\HttpFoundation\Request;

class UserApprovalController extends BaseController
{
    public function approvalsAction(Request $request, $approvalStatus)
    {
        $fields     = $request->query->all();
        $user       = $this->getCurrentUser();
        $conditions = array(
            'roles'          => '',
            'keywordType'    => '',
            'keyword'        => '',
            'approvalStatus' => $approvalStatus
        );
        $conditions = array_merge($conditions, $fields);
        $conditions = $this->fillOrgCode($conditions);

        if (isset($fields['keywordType']) && ($fields['keywordType'] == 'truename' || $fields['keywordType'] == 'idcard')) {
            //根据条件从user_approval表里查找数据
            $approvalcount   = $this->getUserService()->searchApprovalsCount($conditions);
            $profiles        = $this->getUserService()->searchApprovals($conditions, array('id', 'DESC'), 0, $approvalcount);
            $userApprovingId = ArrayToolkit::column($profiles, 'userId');
        } else {
            $usercount       = $this->getUserService()->searchUserCount($conditions);
            $profiles        = $this->getUserService()->searchUsers($conditions, array('id', 'DESC'), 0, $usercount);
            $userApprovingId = ArrayToolkit::column($profiles, 'id');
        }

        //在user表里筛选要求的实名认证状态
        $userConditions = array(
            'userIds'        => $userApprovingId,
            'approvalStatus' => $approvalStatus
        );

        if (!empty($conditions['startDateTime'])) {
            $userConditions['startApprovalTime'] = strtotime($conditions['startDateTime']);
        }

        if (!empty($conditions['endDateTime'])) {
            $userConditions['endApprovalTime'] = strtotime($conditions['endDateTime']);
        }

        $userApprovalcount = 0;

        if (!empty($userApprovingId)) {
            $userApprovalcount = $this->getUserService()->searchUserCount($userConditions);
        }

        $paginator = new Paginator(
            $this->get('request'),
            $userApprovalcount,
            20
        );
        $users = array();

        if (!empty($userApprovingId)) {
            $users = $this->getUserService()->searchUsers(
                $userConditions,
                array('id', 'DESC'),
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
            );
        }

        //最终结果
        $userProfiles = $this->getUserService()->findUserApprovalsByUserIds(ArrayToolkit::column($users, 'id'));
        $userProfiles = ArrayToolkit::index($userProfiles, 'userId');
        return $this->render('TopxiaAdminBundle:User:approvals.html.twig', array(
            'users'          => $users,
            'paginator'      => $paginator,
            'userProfiles'   => $userProfiles,
            'approvalStatus' => $approvalStatus
        ));
    }

    public function approveAction(Request $request, $id)
    {
        list($user, $userApprovalInfo) = $this->getApprovalInfo($request, $id);

        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();

            if ($data['form_status'] == 'success') {
                $this->getUserService()->passApproval($id, $data['note']);
            } elseif ($data['form_status'] == 'fail') {
                if ($this->isPluginInstalled('TeacherAudit')) {
                    $approval = $this->getTeacherAuditService()->getApprovalByUserId($user['id']);

                    if (!empty($approval)) {
                        $this->getTeacherAuditService()->rejectApproval($user['id'], '教师资格申请因实名认证未通过而失败');
                    }
                }

                $this->getUserService()->rejectApproval($id, $data['note']);
            }

            return $this->createJsonResponse(array('status' => 'ok'));
        }

        return $this->render("TopxiaAdminBundle:User:user-approve-modal.html.twig",
            array(
                'user'             => $user,
                'userApprovalInfo' => $userApprovalInfo
            )
        );
    }

    public function viewApprovalInfoAction(Request $request, $id)
    {
        list($user, $userApprovalInfo) = $this->getApprovalInfo($request, $id);

        return $this->render("TopxiaAdminBundle:User:user-approve-info-modal.html.twig",
            array(
                'user'             => $user,
                'userApprovalInfo' => $userApprovalInfo
            )
        );
    }

    protected function getApprovalInfo(Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);

        $userApprovalInfo = $this->getUserService()->getLastestApprovalByUserIdAndStatus($user['id'], 'approving');
        return array($user, $userApprovalInfo);
    }

    public function showIdcardAction($userId, $type)
    {
        $user        = $this->getUserService()->getUser($userId);
        $currentUser = $this->getCurrentUser();

        if (empty($currentUser)) {
            throw $this->createAccessDeniedException();
        }

        $userApprovalInfo = $this->getUserService()->getLastestApprovalByUserIdAndStatus($user['id'], 'approving');

        $idcardPath      = $type === 'back' ? $userApprovalInfo['backImg'] : $userApprovalInfo['faceImg'];
        $imgConverToData = new ImgConverToData;
        $imgConverToData->getImgDir($idcardPath);
        $imgConverToData->img2Data();
        $imgData = $imgConverToData->data2Img();
        echo $imgData;
        exit;
    }

    public function cancelAction(Request $request, $id)
    {
        $this->getUserService()->rejectApproval($id, '管理员撤销');

        if ($this->isPluginInstalled('TeacherAudit')) {
            $approval = $this->getTeacherAuditService()->getApprovalByUserId($id);

            if (!empty($approval)) {
                $this->getTeacherAuditService()->rejectApproval($id, '管理员撤销');
            }
        }

        return $this->createJsonResponse(true);
    }

    protected function getTeacherAuditService()
    {
        return $this->getServiceKernel()->createService('TeacherAudit:TeacherAudit.TeacherAuditService');
    }
}
