<?php


namespace Biz\Group\Dao\Impl;


use Biz\Group\Dao\ThreadCollectDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ThreadCollectDaoImpl extends GeneralDaoImpl implements  ThreadCollectDao
{
    protected $table = 'groups_thread_collect';

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime'),
            'serializes' => array('tagIds'=>'json'),
            'orderbys'   => array('name', 'createdTime','id'),
            'conditions' => array(
                'userId = :userId',
                'threadId = :threadId'
            ),
        );
    }
}