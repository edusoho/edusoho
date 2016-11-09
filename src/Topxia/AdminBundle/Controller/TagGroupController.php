<?php
namespace Topxia\AdminBundle\Controller;

use Topxia\Common\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Topxia\AdminBundle\Controller\BaseController;

class TagGroupController extends BaseController
{
    public function indexAction(Request $request)
    {   
        $tagGroups = $this->getTagService()->findTagGroups();

        return $this->render('TopxiaAdminBundle:TagGroup:index.html.twig',array(
            'tagGroups' => $tagGroups
        ));
    }

    public function createAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $fields = $request->request->all();

            if (isset($fields['tagIds'])) {
                $fields['tagNum'] = count($fields['tagIds']);
            }

            $tagGroup = $this->getTagService()->addTagGroup($fields);

            return $this->render('TopxiaAdminBundle:TagGroup:list-tr.html.twig', array(
                'tagGroup' => $tagGroup
            ));
        }

        return $this->render('TopxiaAdminBundle:TagGroup:tag-group-modal.html.twig');
    }

    public function updateAction(Request $request, $groupId)
    {   
        if ($request->getMethod() == 'POST') {
            $fields = $request->request->all();

            if (empty($fields['scope'])) {
                $fields['scope'] = array();
            } else {
                foreach ($fields['scope'] as &$scope) {
                    if ($scope == 'classroom') {
                        $scope = '班级筛选';
                    }

                    if ($scope == 'course') {
                        $scope = '课程筛选';
                    }
                }
            }

            $tagGroup = $this->getTagService()->updateTagGroup($groupId, $fields);
            
            return $this->render('TopxiaAdminBundle:TagGroup:list-tr.html.twig', array(
                'tagGroup' => $tagGroup
            ));    
        }

        $tagGroup = $this->getTagService()->getTagGroup($groupId);

        return $this->render('TopxiaAdminBundle:TagGroup:tag-group-modal.html.twig', array(
            'tagGroup' => $tagGroup,
        ));
    }

    public function checkNameAction(Request $request)
    {
        $name    = $request->query->get('value');
        $exclude = $request->query->get('exclude');

        $avalieable = $this->getTagService()->isTagGroupNameAvalieable($name, $exclude);

        if ($avalieable) {
            $response = array('success' => true, 'message' => '');
        } else {
            $response = array('success' => false, 'message' => $this->getServiceKernel()->trans('标签组已存在'));
        }

        return $this->createJsonResponse($response);
    }

    public function deleteAction(Request $request, $tagId)
    {
        $flag = $this->getTagService()->deleteTagGroup($tagId);

        if ($flag) {
            return $this->createJsonResponse(true);
        } else {
            return $this->createJsonResponse(false);
        }
    }

    protected function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.TagService');
    }
}
