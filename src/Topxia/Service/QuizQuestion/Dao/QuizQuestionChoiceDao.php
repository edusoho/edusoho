<?php

namespace Topxia\Service\QuizQuestion\Dao;

interface QuizQuestionChoice
{
    public function addQuestionChoice($choice);

    public function deleteQuestionChoice($id);

    public function getQuestionChoice($id);

    public function getQuestionChoicesByQuesitonId($quesitonId);

    public function findQuestionChoicesByIds(array $ids);

    public function deleteQuestionChoicesByIds(array $ids);


}