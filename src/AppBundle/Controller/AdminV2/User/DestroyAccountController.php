<?php

namespace AppBundle\Controller\AdminV2\User;

use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\DestroyAccount\Service\DestroyAccountRecordService;
use Biz\DestroyAccount\Service\DestroyedAccountService;
use Biz\User\Service\UserService;
use Symfony\Component\HttpFoundation\Request;

class DestroyAccountController extends BaseController
{
    public function recordIndexAction(Request $request)
    {
        $conditions = $request->query->all();

        $paginator = new Paginator(
            $request,
            $this->getDestroyAccountRecordService()->countDestroyAccountRecords($conditions),
            20
        );

        $records = $this->getDestroyAccountRecordService()->searchDestroyAccountRecords(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('admin-v2/user/destroy-account/record-list.html.twig', array(
            'records' => $records,
            'paginator' => $paginator,
        ));
    }

    public function recordDetailAction(Request $request, $id)
    {
        $record = $this->getDestroyAccountRecordService()->getDestroyAccountRecord($id);

        return $this->render('admin-v2/user/destroy-account/record-detail.html.twig', array(
            'record' => $record,
        ));
    }

    public function auditAction(Request $request, $id)
    {
        $record = $this->getDestroyAccountRecordService()->getDestroyAccountRecord($id);

        if ($request->getMethod() == 'POST') {
            if ($record['userId'] == $this->getCurrentUser()->getId()) {
                return $this->createJsonResponse(array('success' => false, 'message' => $this->trans('admin_v2.destroy_account.destroyed_account.can_not_manage')));
            }

            $field = $request->request->all();

            if ($field['status'] == 'pass') {
                $this->getDestroyAccountRecordService()->passDestroyAccountRecord($id);
            }

            if ($field['status'] == 'reject') {
                $this->getDestroyAccountRecordService()->rejectDestroyAccountRecord($id, $field['reject_reason']);
            }

            return $this->createJsonResponse(array('success' => true));
        }

        return $this->render('admin-v2/user/destroy-account/audit-modal.html.twig', array(
            'record' => $record,
        ));
    }

    public function indexAction(Request $request)
    {
        $conditions = $request->query->all();
        $conditions = $this->filterConditions($conditions);
        $paginator = new Paginator(
            $this->get('request'),
            $this->getDestroyedAccountService()->countDestroyedAccounts($conditions),
            20
        );
        $destroyedAccounts = $this->getDestroyedAccountService()->searchDestroyedAccounts(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('admin-v2/user/destroy-account/destroyed-list.html.twig', array(
            'destroyedAccounts' => $destroyedAccounts,
            'paginator' => $paginator,
        ));
    }

    public function detailAction(Request $request, $id)
    {
        $destroyAccount = $this->getDestroyedAccountService()->getDestroyedAccount($id);
        $record = $this->getDestroyAccountRecordService()->getDestroyAccountRecord($destroyAccount['recordId']);
        $auditUser = $this->getUserService()->getUser($record['auditUserId']);

        return $this->render('admin-v2/user/destroy-account/destroyed-list-detail.html.twig', array(
            'destroyAccount' => $destroyAccount,
            'record' => $record,
            'auditUser' => $auditUser,
        ));
    }

    protected function filterConditions($conditions)
    {
        if (!empty($conditions['keyword']) && 'id' == $conditions['keywordType']) {
            $conditions['id'] = $conditions['keyword'];
        }

        if (!empty($conditions['keyword']) && 'nickname' == $conditions['keywordType']) {
            $conditions['nicknameLike'] = $conditions['keyword'];
        }

        unset($conditions['keyword']);
        unset($conditions['keywordType']);

        return $conditions;
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return DestroyedAccountService
     */
    protected function getDestroyedAccountService()
    {
        return $this->createService('DestroyAccount:DestroyedAccountService');
    }

    /**
     * @return DestroyAccountRecordService
     */
    protected function getDestroyAccountRecordService()
    {
        return $this->createService('DestroyAccount:DestroyAccountRecordService');
    }
}
