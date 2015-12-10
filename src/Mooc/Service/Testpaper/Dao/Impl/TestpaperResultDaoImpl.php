<?php

namespace Mooc\Service\Testpaper\Dao\Impl;

use Topxia\Service\Testpaper\Dao\Impl\TestpaperResultDaoImpl as BaseTestpaperResultDaoImpl;

class TestpaperResultDaoImpl extends BaseTestpaperResultDaoImpl
{
    public function findUserTestpaperResultsByTestpaperIds(array $testpaperIds, $userId)
    {
        if (empty($testpaperIds)) {
            return array();
        }

        $marks        = str_repeat('?,', count($testpaperIds) - 1).'?';
        $sql          = "SELECT * FROM {$this->table} WHERE testId IN ({$marks}) AND userId = ?; ";
        $parameters   = $testpaperIds;
        $parameters[] = $userId;
        return $this->getConnection()->fetchAll($sql, $parameters);
    }

    protected function _createSearchQueryBuilder($conditions)
    {
        $conditions = array_filter($conditions);

        if (isset($conditions['target'])) {
            $conditions['targetLike'] = $conditions['target'].'%';
        }

        $builder = parent::_createSearchQueryBuilder($conditions);
        $builder
            ->andWhere('checkTeacherId  = :checkTeacherId')
            ->andWhere('checkedTime >= :startTime')
            ->andWhere('checkedTime <= :endTime')
            ->andWhere('target LIKE :targetLike');
        return $builder;
    }
}
