<?php

namespace Biz\User\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class UserFootprintDaoImpl extends GeneralDaoImpl
{
    protected $table = 'user_footprint';

    public function deleteBeforeDate($date)
    {
        $sql = "DELETE FROM {$this->table()} WHERE `date` <= ?";

        return $this->db()->executeUpdate($sql, array($date));
    }

    public function declares()
    {
        return array(
            'serializes' => array(),
            'orderbys' => array(
                'createdTime',
                'updatedTime',
                'date',
            ),
            'timestamps' => array(
                'createdTime',
                'updatedTime',
            ),
            'conditions' => array(
                'userId = :userId',
                'targetId = :targetId',
                'targetType = :targetType',
                'date = :date',
                'date <= :date_LE',
            ),
        );
    }
}
