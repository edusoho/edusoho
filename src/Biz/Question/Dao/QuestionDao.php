<?php

namespace Biz\Question\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface QuestionDao extends AdvancedDaoInterface
{
    public function findQuestionsByIds(array $ids);

    public function findQuestionsByParentId($id);

    public function findQuestionsByCourseSetId($courseSetId);

    public function findQuestionsByCopyId($copyId);

    public function findQuestionsByCategoryIds($categoryIds);

    public function deleteSubQuestions($parentId);

    public function deleteByCourseSetId($courseSetId);

    public function copyQuestionsUpdateSubCount($parentId, $subCount);

    public function getQuestionCountGroupByTypes($conditions);

    public function findBySyncIds(array $syncIds);
}
