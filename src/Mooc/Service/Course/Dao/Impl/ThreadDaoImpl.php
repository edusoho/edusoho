<?php

namespace Mooc\Service\Course\Dao\Impl;

use Topxia\Service\Course\Dao\Impl\ThreadDaoImpl as BaseThreadDaoImpl;

class ThreadDaoImpl extends BaseThreadDaoImpl
{
    protected function createThreadSearchQueryBuilder($conditions)
    {
        $builder = parent::createThreadSearchQueryBuilder($conditions);

        $builder = $builder
            ->andWhere('createdTime > :startTime')
            ->andWhere('createdTime < :endTime');

        return $builder;
    }
}
