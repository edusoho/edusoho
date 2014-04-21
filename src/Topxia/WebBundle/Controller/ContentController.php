<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class ContentController extends BaseController
{

    public function articleShowAction(Request $request, $alias)
    {
        $content = $this->getContentByAlias('article', $alias);
        return $this->render('TopxiaWebBundle:Content:show.html.twig', array(
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
            $conditions, 'latest',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $categoryIds = ArrayToolkit::column($contents, 'categoryId');
        $categories = $this->getCategoryService()->findCategoriesByIds($categoryIds);

        $group = $this->getCategoryService()->getGroupByCode('default');
        $categoryTree = $this->getCategoryService()->getCategoryTree($group['id']);

        return $this->render('TopxiaWebBundle:Content:list.html.twig', array(
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
        return $this->render('TopxiaWebBundle:Content:show.html.twig', array(
            'type' => 'activity',
            'content' => $content,
        ));
    }

    public function activityListAction(Request $request)
    {
        return $this->render('TopxiaWebBundle:Content:list.html.twig', array(
            'type' => 'activity',
        ));
    }

    public function pageShowAction(Request $request, $alias)
    {
        $content = $this->getContentByAlias('page', $alias);

        if ($content['template'] == 'default') {
            $template = 'TopxiaWebBundle:Content:page-show.html.twig';
        } else {
            $alias = $content['alias'] ? : $content['id'];
            $template = "@customize/content/page/{$alias}/index.html.twig";
        }

        return $this->render($template, array('content' => $content));
    }

    public function pageListAction(Request $request)
    {
        return $this->render('TopxiaWebBundle:Content:list.html.twig', array(
            'type' => 'page',
        ));
    }

    private function getContentByAlias($type, $alias)
    {
        if (ctype_digit($alias)) {
            $content = $this->getContentService()->getContent($alias);
        } else {
            $content = $this->getContentService()->getContentByAlias($alias);
        }

        if (empty($content) or ($content['type'] != $type)) {
            throw $this->createNotFoundException();
        }

        return $content;
    }

    private function getContentService()
    {
        return $this->getServiceKernel()->createService('Content.ContentService');
    }

    private function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }

}