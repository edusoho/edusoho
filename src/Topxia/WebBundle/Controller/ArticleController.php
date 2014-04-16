<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class ArticleController extends BaseController
{

	public function indexAction(Request $request)
	{	

		$setting = $this->getSettingService()->get('article', array());
		if (empty($setting)) {
			$setting = array('name' => '资讯频道', 'pageNums' => 20);
		}
		
		$categoryTree = $this->getCategoryService()->getCategoryTree();

		$conditions = array(
			'type' => 'article',
			'status' => 'published'
		);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getArticleService()->searchArticlesCount($conditions),
            $setting['pageNums']
        );

		$latestArticles = $this->getArticleService()->searchArticles(
			$conditions, 'published', 
			$paginator->getOffsetCount(),
            $paginator->getPerPageCount()
		);
		
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
			$featuredConditions,'published',
			0,5
		);

		return $this->render('TopxiaWebBundle:Article:index.html.twig', array(
			'categoryTree' => $categoryTree,
			'latestArticles' => $latestArticles,
			'featuredArticles' => $featuredArticles,
			'paginator' => $paginator
		));
	}

	public function categoryAction(Request $request, $categoryCode)
	{	
		$category = $this->getCategoryService()->getCategoryByCode($categoryCode);

		if (empty($category)) {
			throw $this->createNotFoundException('资讯栏目页面不存在');
		}

		$conditions = array(
			'categoryId' => $category['id'],
			'includeChindren' => true,
		);

		$articles = $this->getArticleService()->searchArticles(
			$conditions,'published',
			0,100
		);

		$categoryTree = $this->getCategoryService()->getCategoryTree();

		$rootCategory = $this->getRootCategory($categoryTree,$category);

		$setting = $this->getSettingService()->get('article', array());

		if (empty($setting)) {
			$setting = array('name' => '资讯频道', 'pageNums' => 20);
		}

        $paginator = new Paginator(
            $this->get('request'),
            $this->getArticleService()->searchArticlesCount($conditions),
            $setting['pageNums']
        );

		foreach ($articles as &$article) {
			$article['category'] = $this->getCategoryService()->getCategory($article['categoryId']);
		}

		return $this->render('TopxiaWebBundle:Article:article-list.html.twig', array(
			'categoryTree' => $categoryTree,
			'categoryCode' => $categoryCode,
			'category' => $category,
			'rootCategory' => $rootCategory,
			'articles' => $articles,
			'paginator' => $paginator
		));
	}

	public function detailAction(Request $request,$id)
	{
		$article = $this->getArticleService()->getArticle($id);

		if (empty($article)) {
			throw $this->createNotFoundException('文章已删除或者未发布！');
		}

		if ($article['status'] != 'published') {
			return $this->createMessageResponse('文章不是发布状态，请查看！');
		}


		$conditions = array(
			'status' => 'published'
		);
		
		$createdTime = $article['createdTime'];
		$articlePrevious = $this->getArticleService()->getArticlePrevious($createdTime);
		$articleNext = $this->getArticleService()->getArticleNext($createdTime);
	
		$articleSetting = $this->getSettingService()->get('article', array());
		$categoryTree = $this->getCategoryService()->getCategoryTree();
		
		$category = $this->getCategoryService()->getCategory($article['categoryId']);
		$tagIdsArray = explode(",", $article['tagIds']);
		$tags = $this->getTagService()->findTagsByIds($tagIdsArray);

		$hottestArticles = $this->getArticleService()->searchArticles($conditions, 'popular' , 0 , 10);
		$this->getArticleService()->hitArticle($id);


		return $this->render('TopxiaWebBundle:Article:detail.html.twig', array(
			'categoryTree' => $categoryTree,
			'hottestArticles' => $hottestArticles,
			'articleSetting' => $articleSetting,
			'articlePrevious' => $articlePrevious,
			'article' => $article,
			'articleNext' => $articleNext,
			'tags' => $tags,
			'categoryName' => $category['name'],
			'categoryCode' => $category['code'],
		));
	}

	public function popularArticlesBlockAction()
	{	
		$conditions = array(
			'type' => 'article',
			'status' => 'published'
		);

		$articles = $this->getArticleService()->searchArticles($conditions, 'popular', 0 , 10);

		return $this->render('TopxiaWebBundle:Article:popular-articles-block.html.twig', array(
			'articles' => $articles
		));
	}

	public function recommendArticlesBlockAction()
	{	
		$conditions = array(
			'type' => 'article',
			'status' => 'published',
			'promoted' => 1
		);

		$articles = $this->getArticleService()->searchArticles($conditions, 'normal', 0 , 10);

		return $this->render('TopxiaWebBundle:Article:recommend-articles-block.html.twig', array(
			'articles' => $articles
		));
	}

	private function getRootCategory($categoryTree, $category)
	{
		$start = false;
		foreach (array_reverse($categoryTree) as $treeCategory) {
			if ($treeCategory['id'] == $category['id']) {
				$start = true;
			}

			if ($start && $treeCategory['depth'] ==1) {
				return $treeCategory;
			}
		}

		return null;
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