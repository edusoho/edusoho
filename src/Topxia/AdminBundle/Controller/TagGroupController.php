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
        }

        return $this->render('TopxiaAdminBundle:TagGroup:tag-group-modal.html.twig');
    }

    public function updateAction(Request $request, $groupId)
    {
        return $this->render('TopxiaAdminBundle:TagGroup:tag-group-modal.html.twig', array(
            'tagGroup' => $tagGroup,
            'groupId'  => $groupId
        ));
    }

    protected function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.TagService');
    }
}
