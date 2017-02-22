<?php


namespace Biz\Task\Dao\Impl;


use Biz\Task\Dao\ViewLogDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ViewLogDaoImpl extends GeneralDaoImpl implements ViewLogDao
{

    protected $table = 'course_task_view';

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime'),
            'orderbys'   => array('name', 'created_time'),
            'conditions' => array(
                'fileType = :fileType',
                'fileStorage = :fileStorage',
                'createdTime  => :startTime',
                'createdTime < :endTime',
            ),
        );
    }


}