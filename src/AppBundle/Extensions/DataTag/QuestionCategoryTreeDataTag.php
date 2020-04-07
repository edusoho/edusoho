<?php

namespace AppBundle\Extensions\DataTag;

use Biz\QuestionBank\Service\QuestionBankService;
use Codeages\Biz\ItemBank\Item\Service\ItemCategoryService;
use Topxia\Service\Common\ServiceKernel;

class QuestionCategoryTreeDataTag
{
    public function getData(array $arguments)
    {
        $bankId = $arguments['bankId'];
        $bank = $this->getQuestionBankService()->getQuestionBank($bankId);

        return json_encode($this->getItemCategoryService()->getItemCategoryTree($bank['itemBankId']));
    }

    /**
     * @return ItemCategoryService
     */
    protected function getItemCategoryService()
    {
        return ServiceKernel::instance()->getBiz()->service('ItemBank:Item:ItemCategoryService');
    }

    /**
     * @return QuestionBankService
     */
    protected function getQuestionBankService()
    {
        return ServiceKernel::instance()->getBiz()->service('QuestionBank:QuestionBankService');
    }
}
