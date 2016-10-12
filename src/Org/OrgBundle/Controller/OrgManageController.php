<?php
namespace Org\OrgBundle\Controller;

use Org\Service\Org\Impl\OrgServiceImpl;
use Topxia\Common\TreeToolkit;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Topxia\AdminBundle\Controller\BaseController;

class OrgManageController extends BaseController
{
    public function indexAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $org  = $this->getOrgService()->getOrg($user['orgId']);
        $orgs = $this->getOrgService()->findOrgsByPrefixOrgCode();

        $treeOrgs     = TreeToolkit::makeTree($orgs, 'seq', $org['parentId']);
        $userIds      = ArrayToolkit::column($orgs, 'createdUserId');
        $createdUsers = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('OrgBundle:OrgManage:index.html.twig', array(
            'orgs'         => $treeOrgs,
            'createdUsers' => $createdUsers
        ));
    }

    public function createAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $org = $request->request->all();
            $this->getOrgService()->createOrg($org);
            return $this->redirect($this->generateUrl('admin_org'));
        }

        $parentId = $request->query->get('parentId', 0);
        $org      = array('parentId' => $parentId);
        return $this->render('OrgBundle:OrgManage:modal.html.twig', array('org' => $org));
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
        $relatedDatas = $this->getOrgService()->findRelatedModuleDatas($id);
        if (empty($relatedDatas)) {
            $this->getOrgService()->deleteOrg($id);
            return $this->createJsonResponse(array('status' => 'success'));
        }
        return $this->createJsonResponse(array('status' => 'error', 'data' => $relatedDatas));
    }

    public function checkCodeAction(Request $request)
    {
        $value   = $request->query->get('value');
        $exclude = $request->query->get('exclude');

        $isAvaliable = $this->getOrgService()->isCodeAvaliable($value, $exclude);

        if ($isAvaliable) {
            $response = array('success' => true, 'message' => '');
        } else {
            $response = array('success' => false, 'message' => $this->getServiceKernel()->trans('编码已被占用,请换一个'));
        }

        return $this->createJsonResponse($response);
    }

    public function checkNameAction(Request $request)
    {
        $parentId    = $request->query->get('parentId');
        $name        = $request->query->get('value');
        $exclude     = $request->query->get('exclude');
        $isAvaliable = $this->getOrgService()->isNameAvaliable($name, $parentId, $exclude);

        if ($isAvaliable) {
            $response = array('success' => true, 'message' => '');
        } else {
            $response = array('success' => false, 'message' => '名称已被占用,请换一个');
        }
        return $this->createJsonResponse($response);
    }

    public function sortAction(Request $request)
    {
        $ids = $request->request->get('ids');
        $this->getOrgService()->sortOrg($ids);
        return $this->createJsonResponse(true);
    }

    /**
     * @param  Request
     * @param  [string]  module 要更新的模块名
     * @return [type]
     */
    public function batchUpdateAction(Request $request, $module)
    {
        if ($request->getMethod() == 'POST') {
            $ids     = $request->request->get('ids');
            $orgCode = $request->request->get('orgCode');
            $this->getOrgService()->batchUpdateOrg($module, $ids, $orgCode);
            return $this->createJsonResponse(true);
        }
        return $this->render('OrgBundle:Org:batch-update-org-modal.html.twig', array('module' => $module));
    }


    /**
     * @return OrgServiceImpl
     */
    protected function getOrgService()
    {
        return $this->getServiceKernel()->createService('Org:Org.OrgService');
    }
}
