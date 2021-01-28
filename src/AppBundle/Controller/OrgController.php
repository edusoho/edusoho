<?php

namespace AppBundle\Controller;

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
        $user = $this->getUser();

        if ($user->isSuperAdmin()) {
            $orgs = $this->getOrgService()->findOrgsByPrefixOrgCode();
        } else {
            $orgs = $this->getOrgService()->findOrgsByPrefixOrgCode($user['orgCode']);
        }

        return $this->createJsonResponse($orgs);
    }

    protected function getOrgService()
    {
        return $this->createService('Org:OrgService');
    }
}
