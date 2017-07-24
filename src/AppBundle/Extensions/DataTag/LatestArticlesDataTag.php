<?php

namespace AppBundle\Extensions\DataTag;

use AppBundle\Common\ArrayToolkit;
use Biz\Article\Service\ArticleService;

class LatestArticlesDataTag extends CourseBaseDataTag implements DataTag
{
    /**
     * 获取最新资讯列表.
     *
     * 可传入的参数：
     *   count    必需 课程数量，取值不能超过100
     *
     *   type:  featured  可选  是否头条
     *          promoted  可选  是否推荐
     *          sticky    可选  是否置顶
     *   categoryId: 分类ID
     *
     * @param array $arguments 参数
     *
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

        if (!empty($arguments['type']) && $arguments['type'] == 'sticky') {
            $conditions['sticky'] = 1;
        }

        if (!empty($arguments['categoryId'])) {
            $conditions['categoryId'] = (int) $arguments['categoryId'];
            $conditions['includeChildren'] = 1;
        }

        $conditions['status'] = 'published';
        $articles = $this->getArticleService()->searchArticles($conditions, 'updated', 0, $arguments['count']);

        $categorise = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($articles, 'categoryId'));

        foreach ($articles as $key => $article) {
            if (empty($article['categoryId'])) {
                continue;
            }

            if (isset($categorise[$article['categoryId']]['id']) && $article['categoryId'] == $categorise[$article['categoryId']]['id']) {
                $articles[$key]['category'] = $categorise[$article['categoryId']];
            }
        }

        return $articles;
    }

    /**
     * @return ArticleService
     */
    private function getArticleService()
    {
        return $this->getServiceKernel()->createService('Article:ArticleService');
    }

    protected function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Article:CategoryService');
    }
}
