<?php
namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Topxia\WebBundle\Controller\BaseController;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class JsonTagsController extends BaseController
{
    public function searchAction(Request $request)
    {
        $type = $request->query->get('type');
        $tags = $this->getTagService()->findAllTags();
        $types = array($type,'public');
        $tagGroups = $this->getTagService()->findTagGroupsByTypes($types);
        $tagSets = $this->getTagSets($tagGroups,$tags);
        return $this->render('TopxiaAdminBundle:Tag2:tag-set.html.twig',array(
            'tagSets' => $tagSets
        ));
    }

    public function matchAction(Request $request)
    {
        $likeString = $request->query->get('q');

        $tags = $this->getTagService()->getTagByLikeName($likeString);
        $tags = $this->getIdsAndNames($tags);

        return $this->createJsonResponse($tags);
    }

    public function queryAction(Request $request)
    {
        $ids = $request->query->get('ids');
        $ids = explode(',', $ids[0]);
        $tags = $this->getTagService()->findTagsByIds($ids);
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

}