<?php

namespace Biz\Thread\Dao\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\Thread\Dao\ThreadDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ThreadDaoImpl extends GeneralDaoImpl implements ThreadDao
{
    protected $table = 'thread';

    public function findThreadIds($conditions)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select('id');

        $result = $builder->execute()->fetchAll(0);

        return !empty($result) ? ArrayToolkit::column($result, 'id') : array();
    }

    public function declares()
    {
        return array(
            'serializes' => array(
                'ats' => 'json',
            ),
            'orderbys' => array(
                'sticky',
                'createdTime',
                'lastPostTime',
                'updateTime',
                'hitNum',
            ),
            'conditions' => array(
                'updateTime >= :updateTime_GE',
                'targetType = :targetType',
                'targetId = :targetId',
                'userId = :userId',
                'type = :type',
                'sticky = :isStick',
                'nice = :nice',
                'postNum = :postNum',
                'postNum > :postNumLargerThan',
                'status = :status',
                'createdTime >= :startTime',
                'createdTime <= :endTime',
                'title LIKE :title',
                'id NOT IN ( :excludeIds )',
                'targetId IN (:targetIds)',
                'startTime > :startTimeGreaterThan',
                'content LIKE :content',
            ),
        );
    }
}
