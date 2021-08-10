<?php

namespace Biz\TeacherQualification\Dao\Impl;

use Biz\TeacherQualification\Dao\TeacherQualificationDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class TeacherQualificationDaoImpl extends GeneralDaoImpl implements TeacherQualificationDao
{
    protected $table = 'teacher_qualification';

    const TEACHER_QUALIFICATION_ORDER_BY = ['updated_time', 'created_time'];

    public function getByUserId($userId)
    {
        return $this->getByFields(['user_id' => $userId]);
    }

    public function findByUserIds($userIds)
    {
        return $this->findInField('user_id', $userIds);
    }

    public function countTeacherQualification($conditions)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select('COUNT(*)')
            ->leftJoin($this->table, 'user', 'u', "u.id = {$this->table}.user_id");

        if (!empty($conditions['roles'])) {
            $builder->andWhere('u.roles like :roles');
        }

        return (int) $builder->execute()->fetchColumn(0);
    }

    public function searchTeacherQualification($conditions, $orderBys, $start, $limit)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select("{$this->table}.*")
            ->leftJoin($this->table, 'user', 'u', "u.id = {$this->table}.user_id")
            ->setFirstResult($start)
            ->setMaxResults($limit);

        if (!empty($conditions['roles'])) {
            $builder->andWhere('u.roles like :roles');
        }

        foreach ($orderBys ?: [] as $order => $sort) {
            if (in_array($order, self::TEACHER_QUALIFICATION_ORDER_BY)) {
                $builder->addOrderBy($this->table.'.'.$order, $sort);
            }
        }

        return $builder->execute()->fetchAll() ?: [];
    }

    public function declares()
    {
        return [
            'timestamps' => [
                'created_time',
                'updated_time',
            ],
            'orderbys' => [
                'updated_time',
                'created_time',
            ],
        ];
    }
}
