<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class ArticleController extends BaseController
{

	public function indexAction(Request $request)
	{
		$categoryTree = $this->getCategoryService()->getCategoryTree();

		$conditions = array(
			'type' => 'article',
			'status' => 'published'
		);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getArticleService()->searchArticleCount($conditions),
            10
        );

		$latestArticles = $this->getArticleService()->searchArticles(
			$conditions, array('createdTime', 'DESC'), 
			$paginator->getOffsetCount(),
            $paginator->getPerPageCount()
		);

		$hottestArticles = $this->getArticleService()->searchArticles($conditions, array('hits' , 'DESC'), 0 , 10);
		
		foreach ($latestArticles as &$article) {
			$article['category'] = $this->getCategoryService()->getCategory($article['categoryId']);

		}

		$featuredConditions = array(
			'type' => 'article',
			'status' => 'published',
			'featured' => 1
		);

		$featuredArticles = $this->getArticleService()->searchArticles(
			$featuredConditions,array('createdTime', 'DESC'),
			0,3
		);

		foreach ($featuredArticles as &$featuredArticle) {
			preg_match('/<img.+src=\"?(.+\.(jpg|gif|bmp|bnp|png))\"?.+>/i', $featuredArticle['body'], $matches);
			if (isset($matches[1])) {
				$featuredArticle['img'] = $matches[1];
			};
		};
		
		return $this->render('TopxiaWebBundle:Article:index.html.twig', array(
			'categoryTree' => $categoryTree,
			'latestArticles' => $latestArticles,
			'hottestArticles' => $hottestArticles,
			'featuredArticles' => $featuredArticles
		));
	}

	/*public function articleListAction(Request $request)
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
	}*/

    private function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Article.CategoryService');
    }

    private function getArticleService()
    {
        return $this->getServiceKernel()->createService('Article.ArticleService');
    }

}