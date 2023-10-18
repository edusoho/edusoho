<?php

namespace Codeages\Biz\ItemBank\AnswerQuestionTag\Service\Impl;

use Codeages\Biz\ItemBank\AnswerQuestionTag\Dao\AnswerQuestionTagDao;
use Codeages\Biz\ItemBank\AnswerQuestionTag\Service\AnswerQuestionTagService;
use Codeages\Biz\ItemBank\BaseService;

class AnswerQuestionTagServiceImpl  extends BaseService implements AnswerQuestionTagService
{
    public function createAnswerQuestionTag($answerQuestionTag)
    {
        return $this->getAnswerQuestionTagDao()->create($answerQuestionTag);
    }

    public function updateAnswerQuestionTag($id, $answerQuestionTag)
    {
        return $this->getAnswerQuestionTagDao()->update($id, $answerQuestionTag);
    }

    public function getByAnswerRecordId($answerRecordId)
    {
       return $this->getAnswerQuestionTagDao()->getByAnswerRecordId($answerRecordId);
    }

    public function updateByAnswerRecordId($answerRecordId, $questionIds)
    {
        return $this->getAnswerQuestionTagDao()->updateByAnswerRecordId($answerRecordId, $questionIds);
    }

    public function deleteByAnswerRecordId($answerRecordId)
    {
        return $this->getAnswerQuestionTagDao()->deleteByAnswerRecordId($answerRecordId);
    }

    /**
     * @return AnswerQuestionTagDao
     */
    protected function getAnswerQuestionTagDao()
    {
        return $this->biz->dao('ItemBank:AnswerQuestionTag:AnswerQuestionTagDao');
    }
}