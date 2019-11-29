<?php

namespace AppBundle\Extensions\DataTag;

use Biz\QuestionBank\Service\CategoryService;
use Topxia\Service\Common\ServiceKernel;

class QuestionBankTreeDataTag
{
    public function getData(array $arguments)
    {
        return $this->getCategoryService()->getCategoryAndBankMixedTree();
    }

    /**
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return ServiceKernel::instance()->getBiz()->service('QuestionBank:CategoryService');
    }
}