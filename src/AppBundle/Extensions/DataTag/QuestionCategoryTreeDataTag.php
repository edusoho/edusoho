<?php

namespace AppBundle\Extensions\DataTag;

use Biz\Question\Service\CategoryService;
use Biz\QuestionBank\Service\QuestionBankService;
use Topxia\Service\Common\ServiceKernel;

class QuestionCategoryTreeDataTag
{
    public function getData(array $arguments)
    {
        $bankId = $arguments['bankId'];

        return json_encode($this->getCategoryService()->getCategoryStructureTree($bankId));
    }

    /**
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return ServiceKernel::instance()->getBiz()->service('Question:CategoryService');
    }

    /**
     * @return QuestionBankService
     */
    protected function getQuestionBankService()
    {
        return ServiceKernel::instance()->getBiz()->service('QuestionBank:QuestionBankService');
    }
}
