<?php


namespace Biz\Group\Dao\Impl;


use Biz\Group\Dao\ThreadDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ThreadDaoImpl extends GeneralDaoImpl implements ThreadDao
{
    protected $table = 'groups_thread';

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime'),
            'serializes' => array('tagIds' => 'json'),
            'orderbys'   => array('isStick', 'postNum', 'createdTime','lastPostTime'),
            'conditions' => array(
                'groupId = :groupId',
                'createdTime > :createdTime',
                'updatedTime >= :updatedTime_GE',
                'isElite = :isElite',
                'isStick = :isStick',
                'type = :type',
                'userId = :userId',
                'status = :status',
                'title like :title',
            )
        );
    }


}