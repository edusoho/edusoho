<?php
namespace Org\OrgBundle\Controller;

use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Topxia\AdminBundle\Controller\BaseController;

class OrgManageController extends BaseController
{
    public function indexAction(Request $request)
    {
        $orgs        = $this->getOrgService()->findOrgsByOrgCode();
        $userIds     = ArrayToolkit::column($orgs, 'createdUserId');
        $createUsers = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('OrgBundle:OrgManage:index.html.twig', array(
            'orgs'        => $orgs,
            'createUsers' => $createUsers
        ));
    }

    public function createAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $org = $request->request->all();
            $this->getOrgService()->createOrg($org);
            return $this->redirect($this->generateUrl('admin_org'));
        }

        $org = array(
            'id'          => 0,
            'name'        => '',
            'code'        => '',
            'description' => '',
            'parentId'    => (int) $request->query->get('parentId', 0),
            'seq'         => 0
        );

        return $this->render('OrgBundle:OrgManage:modal.html.twig', array(
            'org' => $org
        ));
    }

    public function updateAction(Request $request, $id)
    {
        if ($request->getMethod() == 'POST') {
            $org = $request->request->all();
            $this->getOrgService()->updateOrg($id, $org);
            return $this->redirect($this->generateUrl('admin_org'));
        }

        $org = $this->getOrgService()->getOrg($id);
        return $this->render('OrgBundle:OrgManage:modal.html.twig', array(
            'org' => $org
        ));
    }

    public function deleteAction(Request $request, $id)
    {
        $this->getOrgService()->deleteOrg($id);
        return $this->createJsonResponse(true);
    }

    public function checkCodeAction(Request $request)
    {
        $value   = $request->query->get('value');
        $exclude = $request->query->get('exclude');

        $isAvaliable = $this->getOrgService()->isCodeAvaliable($value, $exclude);

        if ($isAvaliable) {
            $response = array('success' => true, 'message' => '');
        } else {
            $response = array('success' => false, 'message' => '编码已被占用,请换一个');
        }

        return $this->createJsonResponse($response);
    }

    protected function getOrgService()
    {
        return $this->getServiceKernel()->createService('Org:Org.OrgService');
    }
}
