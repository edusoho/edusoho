<?php
namespace Custom\Service\Course\Dao\Impl;

use Topxia\Service\Course\Dao\Impl\CourseMemberDaoImpl as BaseCourseMemberDaoImpl;

class CourseMemberDaoImpl extends BaseCourseMemberDaoImpl
{
    protected function _createSearchQueryBuilder($conditions)
    {

        $builder = parent::_createSearchQueryBuilder($conditions);

        $builder = $builder->andWhere('userId IN (:userIds)');

        return $builder;
    }

}