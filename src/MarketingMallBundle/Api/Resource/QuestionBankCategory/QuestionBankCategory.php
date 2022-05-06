<?php

namespace MarketingMallBundle\Api\Resource\QuestionBankCategory;

use ApiBundle\Api\ApiRequest;
use Biz\QuestionBank\Service\CategoryService;
use MarketingMallBundle\Api\Resource\BaseResource;
use ApiBundle\Api\Annotation\ApiConf;

class QuestionBankCategory extends BaseResource
{
    public function search(ApiRequest $request)
    {
        return $this->getQuestionBankCategoryService()->getCategoryStructureTree();
    }

    /**
     * @return CategoryService
     */
    private function getQuestionBankCategoryService()
    {
        return $this->service('QuestionBank:CategoryService');
    }
}