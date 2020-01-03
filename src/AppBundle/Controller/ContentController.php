<?php

namespace AppBundle\Controller;

use Biz\Content\ContentException;
use Biz\Content\Service\ContentService;
use Biz\Taxonomy\Service\CategoryService;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;

class ContentController extends BaseController
{
    public function articleShowAction(Request $request, $alias)
    {
        $content = $this->getContentByAlias('article', $alias);

        return $this->render('content/show.html.twig', array(
            'type' => 'article',
            'content' => $content,
        ));
    }

    public function articleListAction(Request $request)
    {
        $conditions = array(
            'type' => 'article',
            'status' => 'published',
            'promoted' => '1',
            'categoryId' => $request->query->get('categoryId'),
        );
        $conditions = array_filter($conditions);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getContentService()->searchContentCount($conditions),
            10
        );

        $contents = $this->getContentService()->searchContents(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $categoryIds = ArrayToolkit::column($contents, 'categoryId');
        $categories = $this->getCategoryService()->findCategoriesByIds($categoryIds);

        $group = $this->getCategoryService()->getGroupByCode('default');
        $categoryTree = $this->getCategoryService()->getCategoryTree($group['id']);

        return $this->render('content/list.html.twig', array(
            'type' => 'article',
            'contents' => $contents,
            'categories' => $categories,
            'categoryTree' => $categoryTree,
            'paginator' => $paginator,
        ));
    }

    public function activityShowAction(Request $request, $alias)
    {
        $content = $this->getContentByAlias('activity', $alias);

        return $this->render('content/show.html.twig', array(
            'type' => 'activity',
            'content' => $content,
        ));
    }

    public function activityListAction(Request $request)
    {
        return $this->render('content/list.html.twig', array(
            'type' => 'activity',
        ));
    }

    public function pageShowAction(Request $request, $alias)
    {
        $content = $this->getContentByAlias('page', $alias);

        if ('default' == $content['template']) {
            $template = 'content/page-show.html.twig';
        } elseif ('blank' == $content['template']) {
            $template = 'content/blank.html.twig';
        } elseif ('fullBlank' == $content['template']) {
            $template = 'content/full-blank.html.twig';
        } else {
            $alias = $content['alias'] ?: $content['id'];
            $template = "@customize/content/page/{$alias}/index.html.twig";
        }

        return $this->render($template, array('content' => $content));
    }

    public function pageListAction(Request $request)
    {
        return $this->render('content/list.html.twig', array(
            'type' => 'page',
        ));
    }

    protected function getContentByAlias($type, $alias)
    {
        if (ctype_digit($alias)) {
            $content = $this->getContentService()->getContent($alias);
        } else {
            $content = $this->getContentService()->getContentByAlias($alias);
        }

        if (empty($content) || ($content['type'] != $type)) {
            $this->createNewException(ContentException::NOTFOUND_CONTENT());
        }

        return $content;
    }

    /**
     * @return ContentService
     */
    protected function getContentService()
    {
        return $this->getBiz()->service('Content:ContentService');
    }

    /**
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return $this->getBiz()->service('Taxonomy:CategoryService');
    }
}
