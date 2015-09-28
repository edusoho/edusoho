<?php
namespace Custom\AdminBundle\Controller;


use Symfony\Component\HttpFoundation\Request;

class OrganizationController extends BaseController
{
    public function indexAction()
    {
        $organizations = $this->getOrganizationService()->getOrganizationTree();

        return $this->render('CustomAdminBundle:Organization:index.html.twig', array(
            'organizations' => $organizations
        ));
    }

    public function editAction(Request $request, $id)
    {

        if($request->getMethod() == 'POST'){
            $this->getOrganizationService()->updateOrganization($id, $request->request->all());
            return $this->renderTBody();
        }

        $organization = $this->getOrganizationService()->getOrganization($id);

        $organizationTree = $this->getOrganizationService()->getOrganizationTree();



        return $this->render('CustomAdminBundle:Organization:modal.html.twig', array(
            'organization' => $organization,
            'tree' => $organizationTree
        ));
    }

    public function deleteAction(Request $request, $id)
    {

        $organization = $this->getOrganizationService()->getOrganization($id);

        if(empty($organization)){
            return $this->createNotFoundException();
        }
        $childrens = $this->getOrganizationService()->findOrganizationsByParentId($id);
        if ($childrens) {
            return $this->createJsonResponse(array('status' => 'error', 'message'=>'此院系/专业有下属组织，无法删除'));
        }

        $this->getOrganizationService()->deleteOrganization($id);

        return $this->createJsonResponse(array('status' => 'success', 'message'=>'院系/专业已删除' ));

    }

    public function createAction(Request $request)
    {
        if ($request->getMethod() == 'POST'){
            $organization = $request->request->all();
            $organization['createdTime'] = time();

            $this->getOrganizationService()->addOrganization($organization);

            return $this->renderTBody();
        }

        $organization = array(
            'id' => 0,
            'name' => '',
            'code' => '',
            'parentId' => (int) $request->query->get('parentId', 0),
            'description' => '',
        );

        $organizationTree = $this->getOrganizationService()->getOrganizationTree();

        return $this->render('CustomAdminBundle:Organization:modal.html.twig', array(
            'organization' => $organization,
            'tree' => $organizationTree
        ));
    }

    public function checkAction(Request $request, $type)
    {
        $value = $request->query->get('value');
        $exclude = $request->query->get('exclude');
        switch($type){
            case 'code':
                $conditions = array($type => $value);
                break;
            default:
                $conditions = array($type => $value);
                break;
        }
        $orderBy = array('createdTime', 'DESC');
        $schoolOrg = $this->getOrganizationService()->searchOrganizations($conditions, $orderBy, 0, 1);
        if(empty($schoolOrg) || $value == $exclude) {
            $response = array('success' => true, 'message' => '');
        }else{
            $message = $type . "已被其他学校组织使用";
            $response = array('success' => false, 'message' => $message);
        }

        return $this->createJsonResponse($response);

    }

    public function renderTBody()
    {
        $organizations = $this->getOrganizationService()->getOrganizationTree();
        return $this->render('CustomAdminBundle:Organization:tbody.html.twig', array(
            'organizations' => $organizations
        ));
    }

    public function checkParentIdAction(Request $request)
    {
        $selectedParentId = $request->query->get('value');

        $currentId = $request->query->get('currentId');

        if($currentId == $selectedParentId && $selectedParentId != 0){
            $response = array('success' => false, 'message' => '不能选择自己作为父栏目');
        } else {
            $response = array('success' => true, 'message' => '');
        }

        return $this->createJsonResponse($response);
    }

    protected function getOrganizationService()
    {
        return $this->createService('Custom:Organization.OrganizationService');
    }
}