<?php

namespace Biz\ItemBankExercise\Service;

interface ExerciseMemberService
{
    public function count($conditions);

    public function search($conditions, $orderBy, $start, $limit, $columns = array());

    public function isExerciseMember($exerciseId, $userId);

    public function becomeStudent($exerciseId, $userId, $info = []);

    public function addTeacher($exerciseId);

    public function getExerciseMember($exerciseId, $userId);

    public function remarkStudent($exerciseId, $userId, $remark);
}
