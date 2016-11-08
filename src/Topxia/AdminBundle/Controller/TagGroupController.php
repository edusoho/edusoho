<?php
namespace Topxia\AdminBundle\Controller;

use Topxia\Common\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Topxia\AdminBundle\Controller\BaseController;

class TagGroupController extends BaseController
{
    public function indexAction(Request $request)
    {   
        $tagGroups = array(
            array(
            'id'          => '1',
            'name'        => '测试标签组',
            'scope'       => '班级筛选',
            'tagNum'      => '1',
            'createdTime' => '11年11月11日')
        );

        return $this->render('TopxiaAdminBundle:TagGroup:index.html.twig',array(
            'tagGroups' => $tagGroups,
            'groupId'   => '1'
        ));
    }

    public function createAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            var_dump($request->request->all());exit();
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
