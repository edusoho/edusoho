<?php

namespace AppBundle\Extensions\DataTag;

use AppBundle\Common\ArrayToolkit;
use Biz\QuestionBank\Service\CategoryService;
use Biz\QuestionBank\Service\QuestionBankService;
use Topxia\Service\Common\ServiceKernel;

class QuestionBankTreeDataTag
{
    public function getData(array $arguments)
    {
        $tree = $this->getCategoryService()->getCategoryAndBankMixedTree();
        $banks = $this->getQuestionBankService()->findUserManageBanks();
        if (!empty($arguments['selectId']) && !in_array($arguments['selectId'], ArrayToolkit::column($banks, 'id'))) {
            $questionBank = $this->getQuestionBankService()->getQuestionBank($arguments['selectId']);
            $tree[] = array('id' => $questionBank['id'], 'name' => $questionBank['name'], 'selected' => true);
        }

        return $tree;
    }

    /**
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return ServiceKernel::instance()->getBiz()->service('QuestionBank:CategoryService');
    }

    /**
     * @return QuestionBankService
     */
    protected function getQuestionBankService()
    {
        return ServiceKernel::instance()->getBiz()->service('QuestionBank:QuestionBankService');
    }
}
