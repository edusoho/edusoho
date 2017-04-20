<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\ImgConverToData;
use Symfony\Component\HttpFoundation\Request;

class UserApprovalController extends BaseController
{
    public function approvalsAction(Request $request, $approvalStatus)
    {
        $fields = $request->query->all();
        $user = $this->getUser();
        $conditions = array(
            'roles' => '',
            'keywordType' => '',
            'keyword' => '',
            'approvalStatus' => $approvalStatus,
        );
        $conditions = array_merge($conditions, $fields);
        $conditions = $this->fillOrgCode($conditions);

        $conditions['startApprovalTime'] = !empty($conditions['startDateTime']) ? strtotime($conditions['startDateTime']) : '';

        $conditions['endApprovalTime'] = !empty($conditions['endDateTime']) ? strtotime($conditions['endDateTime']) : '';

        if (isset($fields['keywordType']) && ($fields['keywordType'] == 'truename' || $fields['keywordType'] == 'idcard')) {
            //根据条件从user_approval表里查找数据
            $userCount = $this->getUserService()->searchApprovalsCount($conditions);
            $profiles = $this->getUserService()->searchApprovals($conditions, array('id' => 'DESC'), 0, $userCount);
            $userApprovingId = ArrayToolkit::column($profiles, 'userId');
        } else {
            $userCount = $this->getUserService()->countUsers($conditions);
            $profiles = $this->getUserService()->searchUsers($conditions, array('id' => 'DESC'), 0, $userCount);
            $userApprovingId = ArrayToolkit::column($profiles, 'id');
        }

        $paginator = new Paginator(
            $this->get('request'),
            $userCount,
            20
        );

        $users = array();
        if (!empty($userApprovingId)) {
            $users = $this->getUserService()->searchUsers(
                $conditions,
                array('id' => 'DESC'),
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
            );
        }

        //最终结果
        $userProfiles = $this->getUserService()->findUserApprovalsByUserIds(ArrayToolkit::column($users, 'id'));
        $userProfiles = ArrayToolkit::index($userProfiles, 'userId');

        return $this->render('admin/user/approvals.html.twig', array(
            'users' => $users,
            'paginator' => $paginator,
            'userProfiles' => $userProfiles,
            'approvalStatus' => $approvalStatus,
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

        return $this->render('admin/user/user-approve-modal.html.twig',
            array(
                'user' => $user,
                'userApprovalInfo' => $userApprovalInfo,
            )
        );
    }

    public function viewApprovalInfoAction(Request $request, $id)
    {
        list($user, $userApprovalInfo) = $this->getApprovalInfo($request, $id);

        return $this->render('admin/user/user-approve-info-modal.html.twig',
            array(
                'user' => $user,
                'userApprovalInfo' => $userApprovalInfo,
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
        $user = $this->getUserService()->getUser($userId);
        $currentUser = $this->getUser();

        if (empty($currentUser)) {
            throw $this->createAccessDeniedException();
        }

        $userApprovalInfo = $this->getUserService()->getLastestApprovalByUserIdAndStatus($user['id'], 'approving');

        $idcardPath = $type === 'back' ? $userApprovalInfo['backImg'] : $userApprovalInfo['faceImg'];
        $imgConverToData = new ImgConverToData();
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
        return $this->createService('TeacherAudit:TeacherAudit.TeacherAuditService');
    }
}
