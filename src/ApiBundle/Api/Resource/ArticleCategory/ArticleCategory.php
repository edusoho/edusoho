<?php

namespace ApiBundle\Api\Resource\ArticleCategory;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;

class ArticleCategory extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request)
    {
        $categories = $this->getCategoryService()->getCategoryTree($isPublished = true);

        return $categories;
    }

    /**
     * @return \Biz\Article\Service\Impl\CategoryServiceImpl
     */
    protected function getCategoryService()
    {
        return $this->service('Article:CategoryService');
    }
}
