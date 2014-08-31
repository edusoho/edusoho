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
        
        $categoryIds = ArrayToolkit::column($latestArticles, 'categoryId');

        $categories = $this->getCategoryService()->findCategoriesByIds($categoryIds);
        
        $featuredConditions = array(
            'status' => 'published',
            'featured' => 1,
            'hasPicture' => 1
        );
        
        $featuredArticles = $this->getArticleService()->searchArticles(
            $featuredConditions,'normal',
            0, 5
        );
        return $this->render('TopxiaWebBundle:Article:index.html.twig', array(
            'categoryTree' => $categoryTree,
            'latestArticles' => $latestArticles,
            'featuredArticles' => $featuredArticles,
            'paginator' => $paginator,
            'setting' => $setting,
            'categories' => $categories
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
            'includeChildren' => true,
            'status' => 'published'
        );


        $categoryTree = $this->getCategoryService()->getCategoryTree();

        $rootCategory = $this->getRootCategory($categoryTree,$category);

        $subCategories = $this->getSubCategories($categoryTree, $rootCategory);

        $setting = $this->getSettingService()->get('article', array());
        if (empty($setting)) {
            $setting = array('name' => '资讯频道', 'pageNums' => 20);
        }

        $paginator = new Paginator(
            $this->get('request'),
            $this->getArticleService()->searchArticlesCount($conditions),
            $setting['pageNums']
        );

        $articles = $this->getArticleService()->searchArticles(
            $conditions,'published',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $categoryIds = ArrayToolkit::column($articles, 'categoryId');

        $categories = $this->getCategoryService()->findCategoriesByIds($categoryIds);

        return $this->render('TopxiaWebBundle:Article:list.html.twig', array(
            'categoryTree' => $categoryTree,
            'categoryCode' => $categoryCode,
            'category' => $category,
            'rootCategory' => $rootCategory,
            'articles' => $articles,
            'paginator' => $paginator,
            'setting' => $setting,
            'categories' => $categories,
            'subCategories' => $subCategories
        ));
    }

    public function detailAction(Request $request,$id)
    {
        $article = $this->getArticleService()->getArticle($id);

        if (empty($article)) {
            throw $this->createNotFoundException('文章已删除或者未发布！');
        }

        if ($article['status'] != 'published') {
            return $this->createMessageResponse('error','文章不是发布状态，请查看！');
        }

        $setting = $this->getSettingService()->get('article', array());

        if (empty($setting)) {
            $setting = array('name' => '资讯频道', 'pageNums' => 20);
        }

        $conditions = array(
            'status' => 'published'
        );

        $defaultSetting = $this->getSettingService()->get('default', array());
        $site = $this->getSettingService()->get('site', array());

        if (empty($defaultSetting)){
            $articleShareContent = '';
        } else {
            $articleShareContent = $defaultSetting['articleShareContent'];
        }

        $valuesToBeReplace = array('{{articletitle}}', '{{sitename}}');
        $valuesToReplace = array($article['title'], $site['name']);
        $articleShareContent = str_replace($valuesToBeReplace, $valuesToReplace, $articleShareContent);

        $createdTime = $article['createdTime'];

        $currentArticleId = $article['id'];
        $articlePrevious = $this->getArticleService()->getArticlePrevious($currentArticleId);
        $articleNext = $this->getArticleService()->getArticleNext($currentArticleId);
    
        $articleSetting = $this->getSettingService()->get('article', array());
        $categoryTree = $this->getCategoryService()->getCategoryTree();

        $category = $this->getCategoryService()->getCategory($article['categoryId']);
        if(empty($article['tagIds'])){
            $article['tagIds'] = array();
        }
        $tags = $this->getTagService()->findTagsByIds($article['tagIds']);

        $seoKeyword = "";
        if($tags){
            $seoKeyword = ArrayToolkit::column($tags, 'name');
            $seoKeyword = implode(",", $seoKeyword);
        }

        $this->getArticleService()->hitArticle($id);

        $breadcrumbs = $this->getCategoryService()->findCategoryBreadcrumbs($category['id']);

        return $this->render('TopxiaWebBundle:Article:detail.html.twig', array(
            'categoryTree' => $categoryTree,
            'articleSetting' => $articleSetting,
            'articlePrevious' => $articlePrevious,
            'article' => $article,
            'articleNext' => $articleNext,
            'tags' => $tags,
            'setting' => $setting,
            'seoKeyword' => $seoKeyword,
            'seoDesc' => $article['body'],
            'breadcrumbs' => $breadcrumbs,
            'categoryName' => $category['name'],
            'categoryCode' => $category['code'],
            'articleShareContent' => $articleShareContent,
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

    private function getSubCategories($categoryTree, $rootCategory)
    {
        $categories = array();

        $start = false;
        foreach ($categoryTree as $treeCategory) {
            
            if ($start && ($treeCategory['depth'] == 1) && ($treeCategory['id'] != $rootCategory['id'])) {
                break;
            }

            if ($treeCategory['id'] == $rootCategory['id']) {
                $start = true;
            }

            if ($start == true) {
                $categories[] = $treeCategory;
            }

        }

        return $categories;
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