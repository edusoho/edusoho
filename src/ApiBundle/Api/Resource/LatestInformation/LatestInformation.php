<?php


namespace ApiBundle\Api\Resource\LatestInformation;


use ApiBundle\Api\Resource\AbstractResource;
use Biz\Article\Service\ArticleService;

class LatestInformation extends AbstractResource
{
    public function search()
    {
        return $this ->getArticleService() -> searchArticles(['status' => 'published'], ['sticky' => 'DESC' ,'publishedTime' => 'DESC'], 0, 3);
    }

    /**
     * @return ArticleService
     */
    protected function getArticleService()
    {
        return $this->service('Article:ArticleService');
    }
}