<?php

namespace Biz\Thread\Dao\Impl;

use Biz\Thread\Dao\ThreadDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ThreadDaoImpl extends GeneralDaoImpl implements ThreadDao
{
    protected $table = 'thread';

    public function declares()
    {
        return array(
            'timestamps' => array(
                'created_time',
                'updated_time'
            ),
            'serializes' => array(
                'ats' => 'json'
            ),
            'orderbys'   => array(
                'isStick',
                'latestPostTime',
                'createdTime',
                'latestPostTime',
                'hitNum'
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
                'content LIKE :content'
            )
        );
    }

}
