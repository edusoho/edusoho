<?php

namespace Topxia\Api\Resource;

use Biz\Article\Service\ArticleService;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Api\Util\TagUtil;

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
        $res['tags']   = TagUtil::buildTags('article', $res['id']);

        return $res;
    }

    /**
     * @return ArticleService
     */
    protected function getArticleService()
    {
        return $this->getServiceKernel()->createService('Article:ArticleService');
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->createService('System:SettingService');
    }
}
