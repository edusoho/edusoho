<?php

namespace ApiBundle\Api\Resource\Article;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;

class Article extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request)
    {
        $conditions = $request->query->all();
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $sort = $request->query->get('sort', 'published');

        if (!empty($conditions['categoryId'])) {
            $conditions['categoryIds'] = $this->getArticleCategoryService()->findCategoryTreeIds($conditions['categoryId']);
            unset($conditions['categoryId']);
        }
        $conditions['status'] = 'published';

        $total = $this->getArticleService()->countArticles($conditions);
        $articles = $this->getArticleService()->searchArticles($conditions, $sort, $offset, $limit);

        return $this->makePagingObject($articles, $total, $offset, $limit);
    }

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $id)
    {
        $article = $this->getArticleService()->getArticle($id);

        return $article;
    }

    /**
     * @return \Biz\Article\Service\Impl\CategoryServiceImpl
     */
    protected function getArticleCategoryService()
    {
        return $this->service('Article:CategoryService');
    }

    /**
     * @return \Biz\Article\Service\Impl\ArticleServiceImpl
     */
    protected function getArticleService()
    {
        return $this->service('Article:ArticleService');
    }
}
