<?php

namespace Biz\ItemBankExercise\Member;

class TeacherMember extends Member
{
    const ROLE = 'teacher';

    protected function addMember($exercise, $userId, $info)
    {
        $member = [
            'exerciseId' => $exercise['id'],
            'questionBankId' => $exercise['questionBankId'],
            'userId' => $userId,
            'role' => self::ROLE,
            'remark' => '',
        ];

        return $this->getMemberDao()->create($member);
    }

    protected function beforeAdd($exerciseId, $userId, $info)
    {
        return $this->getExerciseService()->tryManageExercise($exerciseId, 0);
    }

    protected function afterAdd($member, $exercise, $info)
    {
        $this->getExerciseService()->update($exercise['id'], ['teacherIds' => array_merge($exercise['teacherIds'], [$member['userId']])]);
    }
}
