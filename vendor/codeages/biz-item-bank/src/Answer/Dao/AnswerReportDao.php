<?php
namespace Codeages\Biz\ItemBank\Answer\Dao;

interface AnswerReportDao
{
    public function findByIds(array $ids);

    public function findByAnswerSceneId($answerSceneId);
}
