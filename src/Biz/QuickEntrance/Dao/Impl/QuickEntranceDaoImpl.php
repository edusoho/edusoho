<?php

namespace Biz\QuickEntrance\Dao\Impl;

use Biz\QuickEntrance\Dao\QuickEntranceDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class QuickEntranceDaoImpl extends AdvancedDaoImpl implements QuickEntranceDao
{
    protected $table = 'quick_entrance';

    public function getByUserId($userId)
    {
        return $this->getByFields(array('userId' => $userId));
    }

    public function declares()
    {
        return array(
            'serializes' => array('data' => 'json'),
            'orderbys' => array(),
            'timestamps' => array('createdTime', 'updatedTime'),
            'conditions' => array(
                'userId = :userId',
            ),
        );
    }
}
