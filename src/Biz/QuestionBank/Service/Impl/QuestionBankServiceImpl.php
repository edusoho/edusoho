<?php

namespace Biz\QuestionBank\Service\Impl;

use Biz\BaseService;
use Biz\QuestionBank\Service\QuestionBankService;

class QuestionBankServiceImpl extends BaseService implements QuestionBankService
{
    public function getQuestionBank($id)
    {
        return $this->getQuestionBankDao()->get($id);
    }

    public function searchQuestionBanks($conditions, $orderBys, $start, $limit, $columns = array())
    {
        return $this->getQuestionBankDao()->search($conditions, $orderBys, $start, $limit, $columns);
    }

    public function countQuestionBanks($conditions)
    {
        return $this->getQuestionBankDao()->count($conditions);
    }

    protected function getQuestionBankDao()
    {
        return $this->createDao('QuestionBank:QuestionBankDao');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
