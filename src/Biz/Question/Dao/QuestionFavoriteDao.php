<?php

namespace Biz\Question\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface QuestionFavoriteDao extends GeneralDaoInterface
{
    public function findUserFavoriteQuestions($userId);

    public function deleteFavoriteByQuestionId($questionId);
}
