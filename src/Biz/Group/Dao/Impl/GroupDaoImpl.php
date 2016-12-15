<?php


namespace Biz\Group\Dao\Impl;


use Biz\Group\Dao\GroupDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class GroupDaoImpl extends GeneralDaoImpl implements GroupDao
{
    protected $table = 'groups';

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime'),
            'serializes' => array('tagIds' => 'json'),
            'orderbys'   => array('createdTime'),
            'conditions' => array(
                'ownerId=:ownerId',
                'status = :status',
                'title like :title'
            ),
        );
    }

    public function getGroupsByIds($ids)
    {
        return $this->findInField('id' , $ids);
    }

}
