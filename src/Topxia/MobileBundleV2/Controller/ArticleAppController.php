<?php

namespace Topxia\MobileBundleV2\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Topxia\WebBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;

class ArticleAppController extends MobileBaseController
{
    public function indexAction(Request $request)
    {
        $setting = $this->getSettingService()->get('article', array());
        if (empty($setting)) {
            $setting = array('name' => '资讯频道', 'pageNums' => 20);
        }

        $categoryId = $request->query->get("categoryId", 0);
        $category = $this->getArticleCategoryService()->getCategory($categoryId);
        $categoryTree = $this->getArticleCategoryService()->getCategoryTree();
        
        return $this->render('TopxiaMobileBundleV2:Article:index.html.twig', array(
            "categoryId"=>$categoryId,
            "category"=>$category,
            "categoryTree"=>$categoryTree
        ));
    }

    public function listAction(Request $request)
    {
        $start = (int) $request->get("start", 0);
        $limit = (int) $request->get("limit", 10);

        $categoryId = $request->get("categoryId");
        $setting = $this->getSettingService()->get('article', array());
        if (empty($setting)) {
            $setting = array('name' => '资讯频道', 'pageNums' => 20);
        }

        $conditions = array(
            'status' => 'published'
        );

        if (!empty($categoryId)) {
            $conditions['categoryId'] = $categoryId;
            $conditions['includeChildren'] = true;
        }
        $latestArticles = $this->getArticleService()->searchArticles($conditions, 'published', $start, $limit);

        return $this->createJson($request, $latestArticles);
    }

    public function detailAction(Request $request, $id)
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
    
        $this->getArticleService()->hitArticle($id);
        return $this->render('TopxiaMobileBundleV2:Article:detail.html.twig', array(
            'articleSetting' => $articleSetting,
            'articlePrevious' => $articlePrevious,
            'article' => $article,
            'articleNext' => $articleNext,
            'articleShareContent' => $articleShareContent,
        )); 
        return $this->createJson($request, array(
            'articleSetting' => $articleSetting,
            'articlePrevious' => $articlePrevious,
            'article' => $article,
            'articleNext' => $articleNext,
            'articleShareContent' => $articleShareContent,
        ));
    }

    private function getArticleService()
    {
        return $this->getServiceKernel()->createService('Article.ArticleService');
    }

    private function getArticleCategoryService()
    {
        return $this->getServiceKernel()->createService('Article.CategoryService');
    }
}
