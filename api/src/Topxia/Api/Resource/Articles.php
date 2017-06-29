<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\ArrayToolkit;

class Articles extends BaseResource
{
    public function get(Application $app, Request $request)
    {
        $conditions = $request->query->all();

        $start = $request->query->get('start', 0);
        $limit = $request->query->get('limit', 20);

        $total = $this->getArticleService()->countArticles($conditions);
        $articles = $this->getArticleService()->searchArticles($conditions, array('publishedTime' => 'DESC'), $start, $limit);
        $articles = $this->assemblyArticles($articles);
        return $this->wrap($this->filter($articles), $total);
    }

    public function filter($res)
    {
        return $this->multicallFilter('Article', $res);
    }

    protected function assemblyArticles(&$articles)
    {
        $categoryIds = ArrayToolkit::column($articles, 'categoryId');
        $categories = $this->getCategoryService()->findCategoriesByIds($categoryIds);

        foreach ($articles as &$article) {
            if (isset($categories[$article['categoryId']])) {
                $article['category'] = array(
                    'id' => $categories[$article['categoryId']]['id'],
                    'name' => $categories[$article['categoryId']]['name'],
                );
            } else {
                $article['category'] = array();
            }
        }

        return $articles;
    }

    protected function multicallFilter($name, $res)
    {
        foreach ($res as $key => $one) {
            $res[$key] = $this->callFilter($name, $one);
        }
        return $res;
    }

    protected function getArticleService()
    {
        return $this->getServiceKernel()->createService('Article:ArticleService');
    }

    protected function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy:TagService');
    }

    protected function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy:CategoryService');
    }
}