<?php

namespace AppBundle\Controller\AdminV2\Developer;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\TreeToolkit;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\Org\Service\OrgService;
use Symfony\Component\HttpFoundation\Request;

class OrgManageController extends BaseController
{
    public function indexAction(Request $request)
    {
        $user = $this->getUser();
        $org = $this->getOrgService()->getOrg($user['orgId']);
        $orgs = $this->getOrgService()->findOrgsByPrefixOrgCode();

        $treeOrgs = TreeToolkit::makeTree($orgs, 'seq', $org['parentId']);
        $userIds = ArrayToolkit::column($orgs, 'createdUserId');
        $createdUsers = $this->getUserService()->findUsersByIds($userIds);

        return $this->render(
            'admin-v2/developer/org-manage/index.html.twig',
            array(
                'orgs' => $treeOrgs,
                'createdUsers' => $createdUsers,
            )
        );
    }

    public function createAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $org = $request->request->all();
            $this->getOrgService()->createOrg($org);

            return $this->redirect($this->generateUrl('admin_v2_org'));
        }

        $parentId = $request->query->get('parentId', 0);
        $org = array('parentId' => $parentId);

        return $this->render('admin-v2/developer/org-manage/modal.html.twig', array('org' => $org));
    }

    public function updateAction(Request $request, $id)
    {
        if ('POST' == $request->getMethod()) {
            $org = $request->request->all();
            $this->getOrgService()->updateOrg($id, $org);

            return $this->redirect($this->generateUrl('admin_v2_org'));
        }

        $org = $this->getOrgService()->getOrg($id);

        return $this->render(
            'admin-v2/developer/org-manage/modal.html.twig',
            array(
                'org' => $org,
            )
        );
    }

    public function deleteAction(Request $request, $id)
    {
        $relatedData = $this->getOrgService()->findRelatedModuleCounts($id);

        if (empty($relatedData)) {
            $this->getOrgService()->deleteOrg($id);

            return $this->createJsonResponse(array('status' => 'success'));
        }

        return $this->createJsonResponse(array('status' => 'error', 'data' => $relatedData));
    }

    public function checkCodeAction(Request $request)
    {
        $value = $request->query->get('value');
        $exclude = $request->query->get('exclude');

        $isAvaliable = $this->getOrgService()->isCodeAvaliable($value, $exclude);

        if ($isAvaliable) {
            $response = array('success' => true, 'message' => '');
        } else {
            $response = array('success' => false, 'message' => $this->trans('编码已被占用,请换一个'));
        }

        return $this->createJsonResponse($response);
    }

    public function checkNameAction(Request $request)
    {
        $parentId = $request->query->get('parentId');
        $name = $request->query->get('value');
        $exclude = $request->query->get('exclude');
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
     * @return OrgService
     */
    protected function getOrgService()
    {
        return $this->createService('Org:OrgService');
    }
}
