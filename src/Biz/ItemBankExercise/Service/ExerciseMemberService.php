<?php

namespace Biz\ItemBankExercise\Service;

interface ExerciseMemberService
{
    public function count($conditions);

    public function search($conditions, $orderBy, $start, $limit, $columns = array());

    public function becomeStudentAndCreateOrder($userId, $courseId, $data);

    public function isExerciseMember($exerciseId, $userId);

    public function becomeStudent($exerciseId, $userId, $info = []);

    public function getExerciseMember($exerciseId, $userId);

    public function remarkStudent($exerciseId, $userId, $remark);
}
