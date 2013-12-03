<?php

namespace Topxia\Service\Quiz\Dao;

interface QuestionChoice
{
    public function addQuestionChoice($choice);

    public function deleteQuestionChoice($id);

    public function getQuestionChoice($id);

    public function getQuestionChoicesByQuesitonId($quesitonId);

    public function findQuestionChoicesByIds(array $ids);

    public function deleteQuestionChoicesByIds(array $ids);


}