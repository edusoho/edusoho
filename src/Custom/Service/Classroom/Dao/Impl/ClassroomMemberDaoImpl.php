<?php


namespace Custom\Service\Classroom\Dao\Impl;

use Classroom\Service\Classroom\Dao\Impl\ClassroomMemberDaoImpl as BaseClassroomMemberDaoImpl;

class ClassroomMemberDaoImpl extends BaseClassroomMemberDaoImpl
{
    public function searchMemberCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');

        return $builder->execute()->fetchColumn(0);
    }

    public function searchMembers($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);

        return $builder->execute()->fetchAll() ?: array();
    }

    protected function _createSearchQueryBuilder($conditions)
    {
        if (isset($conditions['role'])) {
            $conditions['role'] = "%{$conditions['role']}%";
        }

        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'classroom_member')
            ->andWhere('userId = :userId')
            ->andWhere('classroomId = :classroomId')
            ->andWhere('noteNum > :noteNumGreaterThan')
            ->andWhere('role LIKE :role')
            ->andWhere('createdTime >= :startTimeGreaterThan')
            ->andWhere('createdTime < :startTimeLessThan')
            ->andWhere('userId IN (:userIds)')
            ;
        // if (isset($conditions['courseIds'])) {
        //     $courseIds = array();
        //     foreach ($conditions['courseIds'] as $courseId) {
        //         if (ctype_digit($courseId)) {
        //             $courseIds[] = $courseId;
        //         }
        //     }
        //     if ($courseIds) {
        //         $courseIds = join(',', $courseIds);
        //         $builder->andStaticWhere("courseId IN ($courseIds)");
        //     }
        // }

        return $builder;
    }
}