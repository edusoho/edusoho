<?php

namespace Biz\ItemBankExercise\Dao\Impl;

use Biz\ItemBankExercise\Dao\ExerciseMemberDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class ExerciseMemberDaoImpl extends AdvancedDaoImpl implements ExerciseMemberDao
{
    protected $table = 'item_bank_exercise_member';

    public function getByExerciseIdAndUserIdAndRole($exerciseId, $userId, $role)
    {
        return $this->getByFields([
            'exerciseId' => $exerciseId,
            'userId' => $userId,
            'role' => $role,
        ]);
    }

    public function deleteByExerciseId($exerciseId)
    {
        return $this->db()->delete($this->table(), ['exerciseId' => $exerciseId]);
    }

    public function findByUserIdAndRole($userId, $role)
    {
        return $this->findByFields(['userId' => $userId, 'role' => $role]);
    }

    public function findByExerciseIdAndUserId($exerciseId, $userId)
    {
        return $this->findByFields(['exerciseId' => $exerciseId, 'userId' => $userId]);
    }

    public function changeMembersDeadlineByExerciseId($exerciseId, $day)
    {
        $sql = "UPDATE `item_bank_exercise_member` SET `deadline` = `deadline` {$day} WHERE exerciseId = {$exerciseId} AND `role` = 'student';";

        return $this->db()->executeUpdate($sql, [$exerciseId, $day]);
    }

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'orderbys' => ['createdTime', 'updatedTime', 'deadline', 'doneQuestionNum'],
            'conditions' => [
                'id = :id',
                'id NOT IN (:excludeIds)',
                'id IN (:ids)',
                'userId = :userId',
                'userId IN (:userIds)',
                'exerciseId = :exerciseId',
                'exerciseId IN (:exerciseIds)',
                'role = :role',
                'locked = :locked',
                'canLearn = :canLearn',
                'doneQuestionNum > :doneQuestionNum',
                'createdTime >= :startTimeGreaterThan',
                'createdTime < :startTimeLessThan',
                'deadline <= :deadlineLessThen',
                'deadline >= :deadlineGreaterThan',
                'deadline = 0 or deadline > :deadlineAfter',
                'deadline > 0 and deadline <= :deadlineBefore',
                'joinedChannel = :joinedChannel',
                'joinedChannel IN (:joinedChannels)',
            ],
        ];
    }
}
