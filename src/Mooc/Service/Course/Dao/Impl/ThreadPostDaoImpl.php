<?php

namespace Mooc\Service\Course\Dao\Impl;

use Mooc\Service\Course\Dao\ThreadPostDao;
use Topxia\Service\Course\Dao\Impl\ThreadPostDaoImpl as BaseThreadPostDaoImpl;

class ThreadPostDaoImpl extends BaseThreadPostDaoImpl implements ThreadPostDao
{
    public function searchThreadPostCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
                        ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    protected function _createSearchQueryBuilder($conditions)
    {
        $builder = $this->createDynamicQueryBuilder($conditions)
                        ->from($this->table, 'post')
                        ->andWhere('courseId = :courseId')
                        ->andWhere('lessonId = :lessonId')
                        ->andWhere('threadId = :threadId')
                        ->andWhere('userId = :userId')
                        ->andWhere('createdTime >= :startTime')
                        ->andWhere('createdTime <= :endTime');
        return $builder;
    }
}
