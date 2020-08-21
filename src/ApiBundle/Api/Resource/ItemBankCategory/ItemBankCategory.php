<?php

namespace ApiBundle\Api\Resource\ItemBankCategory;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;

class ItemBankCategory extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request)
    {
        return $this->getCategoryService()->getCategoryStructureTree();
    }

    /**
     * @return \Biz\QuestionBank\Service\CategoryService
     */
    private function getCategoryService()
    {
        return $this->service('QuestionBank:CategoryService');
    }
}
