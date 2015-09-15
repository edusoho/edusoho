<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class Article extends BaseResource
{
    public function get(Application $app, Request $request, $id)
    {
        $article = $this->getArticleService()->getArticle($id);

        return $this->filter($article);
    }

    public function filter(&$res)
    {
        $res['createdTime'] = date('c', $res['createdTime']);
        $res['updatedTime'] = date('c', $res['updatedTime']);

        return $res;
    }

    protected function getArticleService()
    {
        return $this->getServiceKernel()->createService('Article.ArticleService');
    }

}