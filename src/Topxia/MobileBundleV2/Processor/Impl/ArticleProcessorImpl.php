<?php
namespace Topxia\MobileBundleV2\Processor\Impl;

use Topxia\MobileBundleV2\Processor\BaseProcessor;
use Topxia\MobileBundleV2\Processor\ArticleProcessor;

class ArticleProcessorImpl extends BaseProcessor implements ArticleProcessor
{
    public function getVersion()
    {
        var_dump($this->request->get('name'));
        return $this->formData;
    }

    public function getAritcleList()
    {
        $start = (int) $this->getParam("start", 0);
        $limit = (int) $this->getParam("limit", 10);

        $setting = $this->getSettingService()->get('article', array());
        if (empty($setting)) {
            $setting = array('name' => '资讯频道', 'pageNums' => 20);
        }

        $categoryTree = $this->getCategoryService()->getCategoryTree();
        $conditions = array('status' => 'published');
        $paginator = new Paginator($this->get('request'), $this->getArticleService()->searchArticlesCount($conditions), $setting['pageNums']);

        $latestArticles = $this->getArticleService()->searchArticles($conditions, 'published', $start, $limit);
        return $latestArticles;
    }
}