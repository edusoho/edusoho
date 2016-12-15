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
            'timestamps' => array('created_time', 'updated_time'),
            'serializes' => array('ats' => 'json'),
            'orderbys'   => array('isStick', 'latestPostTime','createdTime','latestPostTime','hitNum'),
            'conditions' => array(
                'name = :name',
            ),
        );
    }


}