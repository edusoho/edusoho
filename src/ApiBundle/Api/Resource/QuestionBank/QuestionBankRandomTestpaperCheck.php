<?php

namespace ApiBundle\Api\Resource\QuestionBank;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\QuestionBank\Service\QuestionBankService;

class QuestionBankRandomTestpaperCheck extends AbstractResource
{
    public function add(ApiRequest $request, $id)
    {
        $questionBank = $this->getQuestionBankService()->getQuestionBank($id);
        if (empty($questionBank)) {
            return ;
        }

        $data = $request->request->all();
        $data['itemBankId'] = $questionBank['itemBankId'];
        $result = $this->getBiz()['testpaper_builder.random_testpaper']->canBuild($data);

        return $result;
    }

    /**
     * @return QuestionBankService
     */
    protected function getQuestionBankService()
    {
        return $this->service('QuestionBank:QuestionBankService');
    }
}
