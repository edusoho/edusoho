<?php
namespace Org\OrgBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;

class OrgController extends BaseController
{
    public function switchOrgAction(Request $request, $id)
    {
        $org = $this->getOrgService()->switchOrg($id);
        return $this->createJsonResponse(true);
    }

    public function orgTreeJsonAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if ($user->isSuperAdmin()) {
            $orgs = $this->getOrgService()->findOrgsStartByOrgCode();
        } else {
            $orgs = $this->getOrgService()->findOrgsStartByOrgCode($user['orgCode']);
        }

        return $this->createJsonResponse($orgs);
    }

    protected function getOrgService()
    {
        return $this->getServiceKernel()->createService('Org:Org.OrgService');
    }
}
