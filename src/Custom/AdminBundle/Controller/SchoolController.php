<?php
/**
 * Created by PhpStorm.
 * User: retamia
 * Date: 15/9/17
 * Time: 10:27
 */

namespace Custom\AdminBundle\Controller;


use Symfony\Component\HttpFoundation\Request;

class SchoolController extends BaseController
{
    public function indexAction()
    {
        $organizations = $this->getSchoolService()->getSchoolOrganizationTree();

        return $this->render('CustomAdminBundle:School:index.html.twig', array(
            'organizations' => $organizations
        ));
    }

    public function editAction(Request $request, $id)
    {

        if($request->getMethod() == 'POST'){
            $this->getSchoolService()->updateSchoolOrganization($id, $request->request->all());
            return $this->renderTBody();
        }

        $schoolOrganization = $this->getSchoolService()->getSchoolOrganization($id);

        $organizationTree = $this->getSchoolService()->getSchoolOrganizationTree();



        return $this->render('CustomAdminBundle:School:modal.html.twig', array(
            'schoolOrganization' => $schoolOrganization,
            'tree' => $organizationTree
        ));
    }

    public function deleteAction(Request $request, $id)
    {
        if(empty($this->getSchoolService()->getSchoolOrganization($id))){
            return $this->createNotFoundException();
        }
        $childrens = $this->getSchoolService()->findSchoolOrganizationsByParentId($id);
        if ($childrens) {
            return $this->createJsonResponse(array('status' => 'error', 'message'=>'此机构有下属栏目，无法删除'));
        }

        $this->getSchoolService()->deleteSchoolOrganization($id);

        return $this->createJsonResponse(array('status' => 'success', 'message'=>'机构已删除' ));

    }

    public function createAction(Request $request)
    {
        if ($request->getMethod() == 'POST'){
            $organization = $request->request->all();
            $organization['createdTime'] = time();

            $this->getSchoolService()->addSchoolOrganization($organization);

            return $this->renderTBody();
        }

        $schoolOrganization = array(
            'id' => 0,
            'name' => '',
            'code' => '',
            'parentId' => (int) $request->query->get('parentId', 0),
            'description' => '',
        );

        $organizationTree = $this->getSchoolService()->getSchoolOrganizationTree();

        return $this->render('CustomAdminBundle:School:modal.html.twig', array(
            'schoolOrganization' => $schoolOrganization,
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
        $schoolOrg = $this->getSchoolService()->searchSchoolOrganization($conditions, $orderBy, 0, 1);
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
        $organizations = $this->getSchoolService()->getSchoolOrganizationTree();
        return $this->render('CustomAdminBundle:School:tbody.html.twig', array(
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

    protected function getSchoolService()
    {
        return $this->createService('Custom:School.SchoolService');
    }
}