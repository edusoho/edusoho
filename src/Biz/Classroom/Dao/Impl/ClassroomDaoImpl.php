<?php


namespace Biz\Classroom\Dao\Impl;


use Biz\Classroom\Dao\ClassroomDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ClassroomDaoImpl extends GeneralDaoImpl implements ClassroomDao
{
    protected $table = 'classroom';

    public function declares()
    {
        return array(
            'timestamps' => array('created_time', 'updated_time'),
            'serializes' => array('assistantIds' => 'json', 'teacherIds' => 'json', 'service' => 'json'),
            'orderbys'   => array('name', 'created_time'),
            'conditions' => array(
                'name = :name',
            ),
        );
    }

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }


}