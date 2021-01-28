<?php

namespace AppBundle\Extensions\DataTag;

use Biz\Article\Service\ArticleService;
use AppBundle\Common\ArrayToolkit;

class PublishedArticlesDataTag extends CourseBaseDataTag implements DataTag
{
    /**
     * 获取全部资讯列表.
     *
     * 可传入的参数：
     *   count    必需 课程数量，取值不能超过100
     *   sort:     created 创建时间
     *             published 发布时间,带置顶
     *             normal 发布时间
     *             popular 热门
     *
     * @param array $arguments 参数
     *
     * @return array 资讯列表
     */
    public function getData(array $arguments)
    {
        $this->checkCount($arguments);
        $conditions['status'] = 'published';
        $sort = isset($arguments['sort']) ? $arguments['sort'] : 'published';
        $articles = $this->getArticleService()->searchArticles($conditions, $sort, 0, $arguments['count']);

        if (empty($articles)) {
            return array();
        }

        $categorise = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($articles, 'categoryId'));

        foreach ($articles as $key => $article) {
            if (empty($article['categoryId'])) {
                continue;
            }

            if (!empty($categorise[$article['categoryId']]) && $categorise[$article['categoryId']]['id'] == $article['categoryId']) {
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
        return $this->createService('Article:ArticleService');
    }

    protected function getCategoryService()
    {
        return $this->createService('Article:CategoryService');
    }
}
