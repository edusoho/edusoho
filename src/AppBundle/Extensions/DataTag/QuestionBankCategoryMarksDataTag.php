<?php

namespace AppBundle\Extensions\DataTag;

use Biz\QuestionBank\Service\CategoryService;
use Topxia\Service\Common\ServiceKernel;

class QuestionBankCategoryMarksDataTag
{
    public function getData(array $arguments)
    {
        $selectedCategory = $arguments['selectedCategory'];
        $selectedSubCategory = $arguments['selectedSubCategory'];

        return array(
            0 => empty($selectedCategory) ?
                array() : $this->getCategoryService()->getCategory($selectedCategory),
            1 => empty($selectedSubCategory) ?
                array() : $this->getCategoryService()->getCategory($selectedSubCategory),
        );
    }

    /**
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return ServiceKernel::instance()->getBiz()->service('QuestionBank:CategoryService');
    }
}
