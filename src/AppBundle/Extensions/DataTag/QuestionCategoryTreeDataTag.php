<?php

namespace AppBundle\Extensions\DataTag;

use Codeages\Biz\ItemBank\Item\Service\ItemCategoryService;
use Topxia\Service\Common\ServiceKernel;

class QuestionCategoryTreeDataTag
{
    public function getData(array $arguments)
    {
        $bankId = $arguments['bankId'];

        return json_encode($this->getItemCategoryService()->getItemCategoryTree($bankId));
    }

    /**
     * @return ItemCategoryService
     */
    protected function getItemCategoryService()
    {
        return ServiceKernel::instance()->getBiz()->service('ItemBank:Item:ItemCategoryService');
    }
}
