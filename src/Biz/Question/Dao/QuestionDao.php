<?php

namespace Biz\Question\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface QuestionDao extends GeneralDaoInterface
{
    public function findQuestionsByIds(array $ids);

    public function findQuestionsByParentId($id);

    public function findQuestionsByCourseSetId($courseSetId);

    public function deleteSubQuestions($parentId);

    public function deleteByCourseSetId($courseSetId);

    public function copyQuestionsUpdateSubCount($parentId, $subCount);

    public function getQuestionCountGroupByTypes($conditions);
}
