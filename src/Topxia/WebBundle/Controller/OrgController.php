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

    public function orgTreeJsonAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if ($user->isSuperAdmin()) {
            $orgs = $this->getOrgService()->findOrgsByOrgCode();
        } else {
            $orgs = $this->getOrgService()->findOrgsByOrgCode($user['orgCode']);
        }

        return $this->createJsonResponse($orgs);
    }

    protected function getOrgService()
    {
        return $this->getServiceKernel()->createService('Org.OrgService');
    }
}
