<?php

namespace AppBundle\Controller\AdminV2\User;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\ImgConverToData;
use Biz\User\UserException;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\AdminV2\BaseController;

class UserApprovalController extends BaseController
{
    public function approvalsAction(Request $request, $approvalStatus)
    {
        $fields = $request->query->all();

        $conditions = array(
            'roles' => '',
            'keywordType' => '',
            'keyword' => '',
            'approvalStatus' => $approvalStatus,
        );
        $conditions = array_merge($conditions, $fields);
        $conditions = $this->fillOrgCode($conditions);

        $conditions['startApprovalTime'] = !empty($fields['startDateTime']) ? strtotime($fields['startDateTime']) : '';

        $conditions['endApprovalTime'] = !empty($fields['endDateTime']) ? strtotime($fields['endDateTime']) : '';

        $approvals = array();
        if (!empty($fields['keywordType'])) {
            //根据条件从user_approval表里查找数据
            $userCount = $this->getUserService()->searchApprovalsCount($conditions);

            $approvals = $this->getUserService()->searchApprovals($conditions, array('createdTime' => 'ASC'), 0, $userCount);
            $approvals = ArrayToolkit::index($approvals, 'userId');
            $conditions['userIds'] = empty($approvals) ? array(-1) : ArrayToolkit::column($approvals, 'userId');
        }

        $userCount = $this->getUserService()->countUsers($conditions);
        $paginator = new Paginator(
            $this->get('request'),
            $userCount,
            20
        );

        $users = $this->getUserService()->searchUsers(
            $conditions,
            array('id' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        if (!$approvals) {
            $approvals = $this->getUserService()->findUserApprovalsByUserIds(ArrayToolkit::column($users, 'id'));
            $approvals = ArrayToolkit::index($approvals, 'userId');
        }

        return $this->render('admin-v2/user/approval/approvals.html.twig', array(
            'users' => $users,
            'paginator' => $paginator,
            'userProfiles' => $approvals,
            'approvalStatus' => $approvalStatus,
        ));
    }

    public function approveAction(Request $request, $id)
    {
        list($user, $userApprovalInfo) = $this->getApprovalInfo($request, $id, 'approving');

        if ('POST' == $request->getMethod()) {
            $data = $request->request->all();

            if ('success' == $data['form_status']) {
                $this->getUserService()->passApproval($id, $data['note']);
            } elseif ('fail' == $data['form_status']) {
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

        return $this->render('admin-v2/user/approval/user-approve-modal.html.twig',
            array(
                'user' => $user,
                'userApprovalInfo' => $userApprovalInfo,
            )
        );
    }

    public function viewApprovalInfoAction(Request $request, $id)
    {
        list($user, $userApprovalInfo) = $this->getApprovalInfo($request, $id, 'approved');

        return $this->render('admin-v2/user/approval/user-approve-info-modal.html.twig',
            array(
                'user' => $user,
                'userApprovalInfo' => $userApprovalInfo,
            )
        );
    }

    protected function getApprovalInfo(Request $request, $id, $status)
    {
        $user = $this->getUserService()->getUser($id);

        $userApprovalInfo = $this->getUserService()->getLastestApprovalByUserIdAndStatus($user['id'], $status);

        return array($user, $userApprovalInfo);
    }

    public function showIdcardAction(Request $request, $userId, $type)
    {
        $user = $this->getUserService()->getUser($userId);
        $currentUser = $this->getUser();
        $status = $request->query->get('status', 'approving');

        if (empty($currentUser)) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        $userApprovalInfo = $this->getUserService()->getLastestApprovalByUserIdAndStatus($user['id'], $status);

        $idcardPath = 'back' === $type ? $userApprovalInfo['backImg'] : $userApprovalInfo['faceImg'];
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

    /**
     * @return TeacherAudit.TeacherAuditService
     */
    protected function getTeacherAuditService()
    {
        return $this->createService('TeacherAudit:TeacherAudit.TeacherAuditService');
    }
}
