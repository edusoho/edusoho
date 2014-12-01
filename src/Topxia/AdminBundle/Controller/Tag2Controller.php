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

        $tagGroups = $this->getTagService()->findAllTagGroupsByCount(
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

    public function getTagsetAction(Request $request)
    {
        $tags = $this->getTagService()->findAllTags();
        $tagGroups = $this->getTagService()->findAllTagGroups();
        $tagSets = $this->getTagSets($tagGroups,$tags);
        return $this->render('TopxiaAdminBundle:Tag2:tag-set.html.twig',array(
            'tagSets' => $tagSets
        ));
    }

    public function tagsChooseredAction(Request $request)
    {
        $ids = $request->query->get('ids');
        $ids = explode(',', $ids[0]);
        $tags = $this->getTagService()->findTagsByIds($ids);
        return $this->createJsonResponse($tags);
    }

    public function tagsetMatchAction(Request $request)
    {
        $likeString = $request->query->get('q');

        $tags = $this->getTagService()->getTagByLikeName($likeString);
        $tags = $this->getIdsAndNames($tags);

        return $this->createJsonResponse($tags);
    }

    private function getIdsAndNames($tags)
    {
        $array = array();
        foreach ($tags as $key => $tag) {
            $array[$key]['value'] = $tag['id'];
            $array[$key]['label'] = $tag['name'];
        }

        return $array;
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
            if (empty($tag['name'])){
                $response = array('error' => true, 'message' => '请输入标签');
                return $this->createJsonResponse($response);
            }
            $tagCheck = $this->getTagService()->getTagByName($tag['name']);

            if(!empty($tagCheck)){
                $response = array('error' => true, 'message' => '标签已存在');
                return $this->createJsonResponse($response);
            } 

            $tag = $this->getTagService()->updateTag($id, $tag);

            return $this->render('TopxiaAdminBundle:Tag2:tag-manage-modal-tr.html.twig', array(
                'tag' => $tag,
                'id' => $id
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

    public function sourceTagsAction(Request $request)
    {

        $tagGroupCount = $this->getTagService()->getAllGroupCount();

        $tagGroups = $this->getTagService()->findAllTagGroupsByCount(
            $tagGroupCount , $tagGroupCount 
        );

        $tagGroupIds = ArrayToolkit::column($tagGroups,'id');
        $tags = $this->getTagService()->findTagsByTagGroupIds($tagGroupIds);
        $tags = ArrayToolkit::group($tags,'groupId');

        return $this->render('TopxiaAdminBundle:Tag2:tag-source.html.twig', array(
            'tagGroups' => $tagGroups,
            'tags' => $tags,
        ));
    }

    private function getTagSets($tagGroups,$tags)
    {
        $tagSet = array();

        foreach ($tagGroups as $groupkey => $tagGroup) {
            $tagSet[$groupkey] = $tagGroup;
            $tagSet[$groupkey]['subitems'] = array();

            foreach ($tags as $tagkey => $tag) {
                if ($tag['groupId'] == $tagGroup['id']) {
                    $tagSet[$groupkey]['subitems'][] = $tag;
                }
            }
        }

        return $tagSet;
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
