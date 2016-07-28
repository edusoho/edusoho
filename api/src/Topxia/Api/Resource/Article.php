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

    public function filter($res)
    {
        $res['thumb']         = $this->getFileUrl($res['thumb']);
        $res['originalThumb'] = $this->getFileUrl($res['originalThumb']);
        $res['picture']       = $this->getFileUrl($res['picture']);
        $res['body']          = $this->filterHtml($res['body']);
        $res['createdTime']   = date('c', $res['createdTime']);
        $res['updatedTime']   = date('c', $res['updatedTime']);

        $site          = $this->getSettingService()->get('site', array());
        $res['source'] = isset($site['name']) ? $site['name'] : '';

        return $res;
    }

    protected function getArticleService()
    {
        return $this->getServiceKernel()->createService('Article.ArticleService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }
}
