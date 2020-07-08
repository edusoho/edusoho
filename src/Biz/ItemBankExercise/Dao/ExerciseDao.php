<?php

namespace Biz\ItemBankExercise\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface ExerciseDao extends AdvancedDaoInterface
{
    public function getByQuestionBankId($questionBankId);

    public function findByIds($ids);

    public function searchOrderByStudentNumAndLastDays($conditions, $lastDays, $start, $limit);

    public function searchOrderByRatingAndLastDays($conditions, $lastDays, $start, $limit);

    public function findByLikeTitle($title);
}
