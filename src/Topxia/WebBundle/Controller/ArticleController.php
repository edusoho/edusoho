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
			'featured' => 1,
			'hasPicture' => 1
		);
		
		$featuredArticles = $this->getArticleService()->searchArticles(
			$featuredConditions,array('createdTime', 'DESC'),
			0,10
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

	public function detailAction(Request $request,$id)
	{
		$articleSetting = $this->getSettingService()->get('articleSetting', array());
		$categoryTree = $this->getCategoryService()->getCategoryTree();

		$conditions = array(
			'status' => 'published'
		);

		$hottestArticles = $this->getArticleService()->searchArticles($conditions, array('hits' , 'DESC'), 0 , 10);
		$this->getArticleService()->hitArticle($id);

		$conditions['id'] = $id;
		$article = $this->getArticleService()->searchArticles($conditions, array('hits' , 'DESC'), 0 , 1);
		unset($conditions['id']);
		$conditions['idLessThan'] = $id;
		$article_next = $this->getArticleService()->searchArticles($conditions, array('hits' , 'DESC'), 0 , 1);
		unset($conditions['idLessThan']);
		$conditions['idMoreThan'] = $id;
		$article_previous = $this->getArticleService()->searchArticles($conditions, array('hits' , 'DESC'), 0 , 1);
		$article_next = $this->arrayChange($article_next);
		$article_previous = $this->arrayChange($article_previous);
		$article = $this->arrayChange($article);

		if(!empty($article)){
			$category = $this->getCategoryService()->getCategory($article['categoryId']);
			$tagIdsArray = explode(",", $article['tagIds']);
			$tags = $this->getTagService()->findTagsByIds($tagIdsArray);
		}else{
			return $this->createMessageResponse('error', '没有这篇文章！');
		}

		return $this->render('TopxiaWebBundle:Article:detail.html.twig', array(
			'categoryTree' => $categoryTree,
			'hottestArticles' => $hottestArticles,
			'articleSetting' => $articleSetting,
			'article_previous' => $article_previous,
			'article' => $article,
			'article_next' => $article_next,
			'tags' => $tags,
			'categoryName' => $category['name'],
			'categoryCode' => $category['code'],
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

	protected function arrayChange($changeArray){
		if(empty($changeArray)){
			return array();
		}

	    $newArray = array();

	    foreach($changeArray as $valueArr){
	    	foreach ($valueArr as $key => $value) {
	    		$newArray[$key] = $value;
	    	}
	    } 
	    return $newArray; 
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