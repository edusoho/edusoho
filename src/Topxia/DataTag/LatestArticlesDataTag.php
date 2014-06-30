<?php

namespace Topxia\DataTag;

use Topxia\DataTag\DataTag;

class LatestArticlesDataTag extends CourseBaseDataTag implements DataTag  
{

    /**
     * 获取最新课程列表
     *
     * 可传入的参数：
     *   count    必需 课程数量，取值不能超过100
     *   featured  可选  是否头条
     *   promoted  可选  是否推荐
     *   sticky    可选  是否置顶
     *
     * @param  array $arguments 参数
     * @return array 资讯列表
     */
    public function getData(array $arguments)
    {	
        $this->checkCount($arguments);

        $conditions = array();

        if (!empty($arguments['featured'])) {
            $conditions['featured'] = 1;
        }

        if (!empty($arguments['promoted'])) {
            $conditions['promoted'] = 1;
        }

        if (!empty($arguments['sticky'])) {
            $conditions['sticky'] = 1;
        }


    	$articles = $this->getArticleService()->searchArticles($conditions,'created', 0, $arguments['count']);

        return $articles;
    }

    private function getArticleService()
    {
        return $this->getServiceKernel()->createService('Article.ArticleService');
    }

}
