<?php


namespace Biz\Group\Dao\Impl;


use Biz\Group\Dao\MemberDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class MemberDaoImpl extends GeneralDaoImpl implements MemberDao
{
    protected $table = 'groups_member';

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime'),
            'serializes' => array(),
            'orderbys'   => array('name', 'createdTime'),
            'conditions' => array(
                'groupId = :groupId',
                'role = :role',
                'userId = :userId'
            ),
        );
    }

}