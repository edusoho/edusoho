<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class ArticleController extends BaseController
{

	public function indexAction(Request $request)
	{	

		$articleSetting = $this->getSettingService()->get('articleSetting', array());
		if (empty($articleSetting)) {
			$articleSetting = array('name' => '资讯频道', 'pageNums' => 20);
		}
		
		$categoryTree = $this->getCategoryService()->getCategoryTree();

		$conditions = array(
			'type' => 'article',
			'status' => 'published'
		);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getArticleService()->searchArticleCount($conditions),
            $articleSetting['pageNums']
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
			'paginator' => $paginator,
			'articleSetting' => $articleSetting
		));
	}

	public function categoryAction(Request $request, $categoryCode)
	{	
		$articleSetting = $this->getSettingService()->get('articleSetting', array());
		if (empty($articleSetting)) {
			$articleSetting = array('name' => '资讯频道', 'pageNums' => 20);
		}
		$categoryTree = $this->getCategoryService()->getCategoryTree();

		$category = $this->getCategoryService()->getCategoryByCode($categoryCode);

		$topCategory = $this->getTopCategory($categoryTree,$category);

		if ($topCategory['parentId'] != 0) {
			$topCategory = $this->getTopCategory($categoryTree,$topCategory);
		}

		$categoryIds = $this->getCategoryService()->findCategoryChildrenIds($category['id']);

		$categoryIds[] = $category['id']; 

        $paginator = new Paginator(
            $this->get('request'),
            $this->getArticleService()->findArticlesCount($categoryIds),
            $articleSetting['pageNums']
        );

		$articles = $this->getArticleService()->findArticlesByCategoryIds(
			$categoryIds, 
			$paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

		foreach ($articles as &$article) {
			$article['category'] = $this->getCategoryService()->getCategory($article['categoryId']);
		}

		return $this->render('TopxiaWebBundle:Article:article-list.html.twig', array(
			'categoryTree' => $categoryTree,
			'categoryCode' => $categoryCode,
			'category' => $category,
			'articleSetting' => $articleSetting,
			'topCategory' => $topCategory,
			'articles' => $articles,
			'paginator' => $paginator
		));
	}

	public function detailAction(Request $request,$id,$categoryCode)
	{
		$articleSetting = $this->getSettingService()->get('articleSetting', array());

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
		
		$article = $this->getArticleService()->getArticle($id);

		$tagIdsArray = explode(",", $article['tagIds']);

		$tags = $this->getTagService()->findTagsByIds($tagIdsArray);

		$category = $this->getCategoryService()->getCategoryByCode($categoryCode);


		return $this->render('TopxiaWebBundle:Article:detail.html.twig', array(
			'categoryTree' => $categoryTree,
			'latestArticles' => $latestArticles,
			'hottestArticles' => $hottestArticles,
			'paginator' => $paginator,
			'articleSetting' => $articleSetting,
			'article' => $article,
			'tags' => $tags,
			'categoryName' => $category['name']
		));
	}

	private function getTopCategory($categoryTree,$category)
	{
		if ($category['parentId'] == 0) {
			return $category;
		} else {
			foreach ($categoryTree as $cat) {
				if ($cat['id'] == $category['parentId']) {
					return $cat;
				}
			}
		}
	}

    private function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Article.CategoryService');
    }

    private function getArticleService()
    {
        return $this->getServiceKernel()->createService('Article.ArticleService');
    }

    private function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

     private function getTagService()
     {
	    return $this->getServiceKernel()->createService('Taxonomy.TagService');
     }

}