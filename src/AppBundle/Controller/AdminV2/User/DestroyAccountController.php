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
        return $this->render('admin-v2/user/destroy-account/record-list.html.twig', array(
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
