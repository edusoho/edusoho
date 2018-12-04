<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use Biz\Taxonomy\Service\TagService;
use Biz\Taxonomy\TagException;
use Symfony\Component\HttpFoundation\Request;

class TagController extends BaseController
{
    public function indexAction(Request $request)
    {
        $total = $this->getTagService()->searchTagCount($conditions = array());
        $paginator = new Paginator($request, $total, 20);
        $tags = $this->getTagService()->searchTags(
            $conditions = array(),
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        foreach ($tags as &$tag) {
            $tagGroups = $this->getTagService()->findTagGroupsByTagId($tag['id']);

            $groupNames = ArrayToolkit::column($tagGroups, 'name');
            if (!empty($groupNames)) {
                $tag['groupNames'] = $groupNames;
            } else {
                $tag['groupNames'] = array();
            }
        }

        return $this->render(
            'admin/tag/index.html.twig',
            array(
                'tags' => $tags,
                'paginator' => $paginator,
            )
        );
    }

    public function createAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $tag = $this->getTagService()->addTag($request->request->all());

            $tagRelation = $this->getTagService()->findTagRelationsByTagIds(array($tag['id']));

            return $this->render(
                'admin/tag/list-tr.html.twig',
                array(
                    'tag' => $tag,
                    'tagRelations' => $tagRelation,
                )
            );
        }

        return $this->render(
            'admin/tag/tag-modal.html.twig',
            array(
                'tag' => array('id' => 0, 'name' => ''),
            )
        );
    }

    public function updateAction(Request $request, $id)
    {
        $tag = $this->getTagService()->getTag($id);

        if (empty($tag)) {
            $this->createNewException(TagException::NOTFOUND_TAG());
        }

        if ('POST' == $request->getMethod()) {
            $tag = $this->getTagService()->updateTag($id, $request->request->all());

            return $this->render(
                'admin/tag/list-tr.html.twig',
                array(
                    'tag' => $tag,
                )
            );
        }

        return $this->render(
            'admin/tag/tag-modal.html.twig',
            array(
                'tag' => $tag,
            )
        );
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

        $avaliable = $this->getTagService()->isTagNameAvailable($name, $exclude);

        if ($avaliable) {
            $response = array('success' => true, 'message' => '');
        } else {
            $response = array('success' => false, 'message' => '标签已存在');
        }

        return $this->createJsonResponse($response);
    }

    /**
     * @return TagService
     */
    protected function getTagService()
    {
        return $this->createService('Taxonomy:TagService');
    }

    protected function getTagWithException($tagId)
    {
        $tag = $this->getTagService()->getTag($tagId);

        if (empty($tag)) {
            $this->createNewException(TagException::NOTFOUND_TAG());
        }

        return $tag;
    }
}
