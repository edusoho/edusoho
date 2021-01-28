<?php

namespace AppBundle\Controller\AdminV2\System;

use AppBundle\Controller\AdminV2\BaseController;
use Biz\Org\Service\OrgService;
use Symfony\Component\HttpFoundation\Request;

class OrgManageController extends BaseController
{
    /**
     * @param  Request
     * @param  [string]  module 要更新的模块名
     *
     * @return [type]
     */
    public function batchUpdateAction(Request $request, $module)
    {
        if ('POST' == $request->getMethod()) {
            $ids = $request->request->get('ids');
            $orgCode = $request->request->get('orgCode');
            $this->getOrgService()->batchUpdateOrg($module, $ids, $orgCode);

            return $this->createJsonResponse(true);
        }

        return $this->render('org/batch-update-org-modal.html.twig', array('module' => $module));
    }

    /**
     * @return OrgService
     */
    protected function getOrgService()
    {
        return $this->createService('Org:OrgService');
    }
}
