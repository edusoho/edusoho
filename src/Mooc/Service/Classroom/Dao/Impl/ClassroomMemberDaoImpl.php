<?php


namespace Mooc\Service\Classroom\Dao\Impl;

use Classroom\Service\Classroom\Dao\Impl\ClassroomMemberDaoImpl as BaseClassroomMemberDaoImpl;

class ClassroomMemberDaoImpl extends BaseClassroomMemberDaoImpl
{
    protected function _createSearchQueryBuilder($conditions)
    {
        $builder = parent::_createSearchQueryBuilder($conditions)
            ->andWhere('userId IN (:userIds)')
            ;

        return $builder;
    }
}