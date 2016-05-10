<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class OrgController extends BaseController
{
    public function switchOrgAction(Request $request, $id)
    {
        $org = $this->getOrgService()->switchOrg($id);
        return $this->createJsonResponse(true);
    }

    protected function getOrgService()
    {
        return $this->getServiceKernel()->createService('Org.OrgService');
    }
}
