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

    protected function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.TagService');
    }
}
