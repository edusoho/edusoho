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
            5
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
			'featuredArticles' => $featuredArticles,
			'paginator' => $paginator
		));
	}

	public function categoryAction(Request $request, $categoryCode)
	{
		$categoryTree = $this->getCategoryService()->getCategoryTree();

		$category = $this->getCategoryService()->getCategoryByCode($categoryCode);

		$categoryChildrenIds = $this->getCategoryService()->findCategoryChildrenIds($category['id']);

		$categories = $this->getCategoryService()->findCategoriesByIds($categoryChildrenIds);

		return $this->render('TopxiaWebBundle:Article:article-list.html.twig', array(
			'categoryTree' => $categoryTree,
			'categoryCode' => $categoryCode,
			'category' => $category,
			'categories' => $categories
		));
	}

    private function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Article.CategoryService');
    }

    private function getArticleService()
    {
        return $this->getServiceKernel()->createService('Article.ArticleService');
    }

}