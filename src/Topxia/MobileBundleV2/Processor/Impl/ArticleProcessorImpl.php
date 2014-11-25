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

        $conditions = array('status' => 'published');
        $latestArticles = $this->getArticleService()->searchArticles($conditions, 'published', $start, $limit);

        return $this->controller->render('TopxiaMobileBundleV2:Article:list.html.twig', array(
            'latestArticles' => $latestArticles
        ));
    }
}