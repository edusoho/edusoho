<?php

namespace Topxia\DataTag;

use Topxia\DataTag\DataTag;
use Topxia\Common\ArrayToolkit;

class LatestArticlesDataTag extends CourseBaseDataTag implements DataTag  
{

    /**
     * 获取最新资讯列表
     *
     * 可传入的参数：
     *   count    必需 课程数量，取值不能超过100
     *   
     *   type:  featured  可选  是否头条
     *          promoted  可选  是否推荐
     *          sticky    可选  是否置顶
     *
     * @param  array $arguments 参数
     * @return array 资讯列表
     */
    public function getData(array $arguments)
    {	
        $this->checkCount($arguments);

        $conditions = array();
        if (!empty($arguments['type']) && $arguments['type'] == 'featured') {
            $conditions['featured'] = 1;
        }

        if (!empty($arguments['type']) && $arguments['type'] == 'promoted') {
            $conditions['promoted'] = 1;
        }

        if (!empty($arguments['type']) && $arguments['type'] == 'sticky' ) {
            $conditions['sticky'] = 1;
        }

    	$articles = $this->getArticleService()->searchArticles($conditions,'created', 0, $arguments['count']);

        $categorise = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($articles, 'categoryId'));


        foreach ($articles as $key => $article) {
            if (empty($article['categoryId'])) {
                continue;
            }
            
            if ($article['categoryId'] == $categorise[$article['categoryId']]['id']) {

                $articles[$key]['category'] = $categorise[$article['categoryId']];
            }
        }

        return $articles;
    }

    private function getArticleService()
    {
        return $this->getServiceKernel()->createService('Article.ArticleService');
    }

    protected function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Article.CategoryService');
    }

}
