<?php

namespace Biz\Question\Dao;

interface QuestionFavoriteDao
{
    public function findUserFavoriteQuestions($userId);

    public function deleteFavoriteByQuestionId($questionId);
}
