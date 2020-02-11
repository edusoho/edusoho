<?php

namespace AppBundle\Controller\AdminV2\User;

use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\DestroyAccount\Service\DestroyAccountRecordService;
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
            array(),
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

    public function indexAction(Request $request)
    {
        return $this->render('admin-v2/user/destroy-account/destroyed-list.html.twig', array(
        ));
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return DestroyAccountRecordService
     */
    protected function getDestroyAccountRecordService()
    {
        return $this->createService('DestroyAccount:DestroyAccountRecordService');
    }
}
