<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\AdminBundle\Controller\BaseController;

class Tag2Controller extends BaseController
{
    public function indexAction(Request $request)
    {
        $tagGroupCount = $this->getTagService()->getAll2GroupCount();

        $paginator = new Paginator(
            $request, 
            $tagGroupCount, 
            20
        );

        $tagGroups = $this->getTagService()->findAllTag2Groups(
            $paginator->getOffsetCount(), $paginator->getPerPageCount()
        );

        $tagGroupIds = ArrayToolkit::column($tagGroups,'id');
        $tags = $this->getTagService()->findTagsByTagGroupIds($tagGroupIds);
        $tags = ArrayToolkit::group($tags,'groupId');

        return $this->render('TopxiaAdminBundle:Tag2:index.html.twig', array(
            'tagGroups' => $tagGroups,
            'tags' => $tags,
            'paginator' => $paginator
        ));
    }

    public function listAction(Request $request,$id)
    {
        $tagGroup = $this->getTagService()->getTagGroup($id);
        $tags = $this->getTagService()->findTagsByTagGroupIds(array($id));

        return $this->render('TopxiaAdminBundle:Tag2:tag-manage-modal.html.twig',array(
            'tagGroup' => $tagGroup,
            'tags' => $tags,
        ));
    }

    public function createAction(Request $request,$id)
    {
        if ('POST' == $request->getMethod()){
            $tag = $request->request->get('name');
            $tag = $this->getTagService()->addTag($tag,$id);
        }

        return $this->render('TopxiaAdminBundle:Tag2:tag-manage-modal-tr.html.twig',array(
            'tag' => $tag,
        ));
    }

    public function groupCreateAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $tagGroup['type'] = $request->request->get('type');
            $tagGroup['name'] = $request->request->get('name');

            $tagGroup = $this->getTagService()->addTagGroup($tagGroup);
            $groupId = $tagGroup['id'];
            $tags = $this->getTagService()->findTagsByTagGroupIds(array($groupId));

            return $this->render('TopxiaAdminBundle:Tag2:list-tr.html.twig', array(
                'tagGroup' => $tagGroup,
                'tag' =>$tags
            ));
        }

        return $this->render('TopxiaAdminBundle:Tag2:tag-modal.html.twig');
    }

    public function updateAction(Request $request, $id)
    {
        if ('POST' == $request->getMethod()) {
            $tag = $request->request->all();
            $tag = $this->getTagService()->updateTag($id, $tag);

            return $this->render('TopxiaAdminBundle:Tag2:tag-manage-modal-tr.html.twig', array(
                'tag' => $tag
            ));
        }
    }

    public function groupUpdateAction(Request $request, $id)
    {
        $tagGroup = $this->getTagService()->getTagGroup($id);
        if (empty($tagGroup)) {
            throw $this->createNotFoundException();
        }

        if ('POST' == $request->getMethod()) {
            $tagGroup = $this->getTagService()->updateTagGroup($id, $request->request->all());
            $tags = $this->getTagService()->findTagsByTagGroupIds(array($id));

            return $this->render('TopxiaAdminBundle:Tag2:list-tr.html.twig', array(
                'tagGroup' => $tagGroup,
                'tag' => $tags
            ));
        }

        return $this->render('TopxiaAdminBundle:Tag2:tag-modal.html.twig', array(
            'tagGroup' => $tagGroup
        ));
    }

    public function groupDeleteAction(Request $request, $id)
    {
        $this->getTagService()->deleteTagGroup($id);
        return $this->createJsonResponse(true);
    }

    public function deleteAction(Request $request, $id)
    {
        $this->getTagService()->deleteTag($id);
        return $this->createJsonResponse(true);
    }

    public function checkNameAction(Request $request)
    {
        $name = $request->query->get('value');
        $exclude = $request->query->get('exclude');

        $avaliable = $this->getTagService()->isTagNameAvalieable($name, $exclude);

        if ($avaliable) {
            $response = array('success' => true, 'message' => '');
        } else {
            $response = array('success' => false, 'message' => '标签已存在');
        }

        return $this->createJsonResponse($response);
    }

    public function checkGroupNameAction(Request $request)
    {
        $name = $request->query->get('value');
        $exclude = $request->query->get('exclude');

        $avaliable = $this->getTagService()->isTagGroupNameAvalieable($name, $exclude);

        if ($avaliable) {
            $response = array('success' => true, 'message' => '');
        } else {
            $response = array('success' => false, 'message' => '标签组已存在');
        }

        return $this->createJsonResponse($response);
    }

    private function getTagService()
    {
        return $this->getServiceKernel()->createService('Tag.TagService');
    }

    private function getTagWithException($tagId)
    {
        $tag = $this->getTagService()->getTag($tagId);
        if (empty($tag)) {
            throw $this->createNotFoundException('标签不存在!');
        }
        return $tag;
    }

}
