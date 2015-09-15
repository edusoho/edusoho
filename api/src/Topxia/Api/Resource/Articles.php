<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

class Articles extends BaseResource
{
    public function get(Application $app, Request $request)
    {
        $conditions = ArrayToolkit::parts($request->query->all(), array('categoryId'));

        $sort = $request->query->get('sort', 'published');
        $start = $request->query->get('start', 0);
        $limit = $request->query->get('limit', 10);

        $articles = $this->getArticleService()->searchArticles($conditions, $sort, $start, $limit);

        $total = $this->getArticleService()->searchArticlesCount($conditions);

        return $this->wrap($this->filter($articles), $total);
    }

    public function filter(&$res)
    {
        return $this->multicallFilter('Article', $res);
    }

    protected function getArticleService()
    {
        return $this->getServiceKernel()->createService('Article.ArticleService');
    }
}